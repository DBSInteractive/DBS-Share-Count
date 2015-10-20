<?php
/**
 * Builds Share URLS and gets social count of urls from the top social media platforms.
 * This also uses WordPress Transients to cache the social media API requests.
 * For more information on WordPress Transients visit: https://codex.wordpress.org/Transients_API
 *
 * @author  DBS>Interactive - 2015-07-08
 * @license CC BY-SA 4.0 - http://creativecommons.org/licenses/by-sa/4.0/ - DBS>Interactive
 * @example
 *
 * // require this php file in Functions.php
 *
 * // Instantiate new DBSShareCount class and pass it the required options.
 * $options = array(
 *    "share_url" => WP_SITEURL . $_SERVER['REQUEST_URI'], // REQUIRED
 *    "share_title" => get_the_title() . " at @the_most_awesome_company", // Optional
 *    "share_text" => "Check out " . get_the_title() . " @the_most_awesome_company", // Optional
 *    "twitter_summary" => "Check out " . get_the_title() . " @the_most_awesome_company", // Optional
 *    "media_url" => $share_media, // Optional
 *    "timeout" => 4 // Optional
 * );
 *
 * $sharecount = new DBSShareCount( $options );
 *
 *
 * // IN YOUR TEMPLATE FILES
 * <li class="facebook">
 *     <a href="<?php echo $sharecount->get_facebook_url(); ?>" title="Share on Facebook">
 *         Like <span class="count"><?php echo $sharecount->get_fb_likes(); ?></span>
 *     </a>
 * </li>
 */

class DBSShareCount {
    
    private $url,
            $timeout,
            $share_url,
            $share_title,
            $share_text,
            $twitter_summary,
            $media_url;

    private $defaults = array (
        "share_url" => WP_SITEURL . $_SERVER['REQUEST_URI'],
        "media_url" => "",
        "share_title" => "",
        "share_text" => "",
        "twitter_summary" => "",
        "timeout" => 4 // in hours.
    );

    function __construct( $options = array() ){
        $this->options 			= array_merge( $options, $this->defaults );
        
        $this->share_title 		= rawurlencode( $options["share_title"] );
        $this->share_text 		= rawurlencode( $options["share_text"] );
        $this->twitter_summary 	= rawurlencode( $options["twitter_summary"] );
        $this->url 				= rawurlencode( $options["share_url"] );
        $this->media_url 		= $options["media_url"] ;
        $this->timeout 			= $options["timeout"];
    }





    /**
     * Returns Correct Twitter Share url
     * @return string Share URL
     */
    function get_twitter_url(){
        return "http://twitter.com/share?url=" . $this->url . "&text=" . $this->twitter_summary;
    }

    /**
     * Returns Correct Facebook Share url
     * @return string Share URL
     */
    function get_facebook_url(){
        return "https://www.facebook.com/sharer/sharer.php?s=100&p[url]=" . $this->url . "&p[images][0]=" . $this->media_url . "&p[title]=" . $this->share_title . "&p[summary]=" . $this->share_text;
    }

    /**
     * Returns Correct Google Plus Share url
     * @return string Share URL
     */
    function get_google_url(){
        return "https://plusone.google.com/_/+1/confirm?hl=en&url=" . $url;
    }

    /**
     * Returns Correct Pinterest Share url
     * @return string Share URL
     */
    function get_pinterest_url(){
        return "http://pinterest.com/pin/create/button/?url=" . $url . "&media=" . $media_url . "&description=" . $share_text;
    }

    /**
     * Returns correct linkedin share url
     * @return string Share URL
     */
    function get_linkedin_url(){
    	return "https://www.linkedin.com/shareArticle?mini=true&url=" . $url . "&title=" . $this->share_title . "&summary=" . $share_text;
    }

    /**
     * Returns correct mail share URL
     * @return string Share URL
     */
    function get_mail_url(){
    	return "mailto:?&subject=" . $this->share_title . "&body=".$share_text."";
    }




    /**
     * Gets Twitter Share count
     * @return int Share Count Number
     */
    function get_twitter() {
        if( $this->is_transient("twitter") ){
            $this->dbs_get_transient("twitter");
            
            return isset( $json['count'] ) ? intval( $json['count'] ) : 0;
        } else {
            $json_string = $this->file_get_contents_curl('http://urls.api.twitter.com/1/urls/count.json?url=' . $this->url);
            $json = json_decode($json_string, true);
            $this->store_transient("twitter");
            
            return isset( $json['count'] ) ? intval( $json['count'] ) : 0;
        }
    }

    /**
     * Gets Facebook Like count
     * @return int Share Count Number
     */
    function get_fb_likes() {
        if( $this->is_transient("fb_likes") ){
            $this->dbs_get_transient("fb_likes");
            
            return isset( $json[0]['like_count'] ) ? intval( $json[0]['like_count'] ) : 0;
        } else {
            $json_string = $this->file_get_contents_curl('http://api.facebook.com/restserver.php?method=links.getStats&format=json&urls='.$this->url);
            $json = json_decode($json_string, true);
            $this->store_transient("fb_likes");
            
            return isset( $json[0]['like_count'] ) ? intval($json[0]['like_count']) : 0;
        }
    }

    /**
     * Gets Facebook Share count
     * @return int Share Count Number
     */
    function get_fb_shares() {
        if( $this->is_transient("fb_shares") ){
            $data = $this->dbs_get_transient("fb_shares");

            return isset($json[0]['share_count'])?intval($json[0]['share_count']):0;
        } else {
            $json_string = $this->file_get_contents_curl('http://api.facebook.com/restserver.php?method=links.getStats&format=json&urls='.$this->url);
            $json = json_decode($json_string, true);
            $this->store_transient("fb_shares");

            return isset( $json[0]['share_count'] ) ? intval( $json[0]['share_count'] ) : 0;
        }
    }

    /**
     * Gets Google Plus +1 count
     * @return int Share Count Number
     */
    function get_plusones()  {
        if( $this->is_transient("plusones") ){
            $data = $this->dbs_get_transient("plusones");
            
            return isset( $json[0]['result']['metadata']['globalCounts']['count'] ) ? intval( $json[0]['result']['metadata']['globalCounts']['count'] ) : 0;
        } else {
            $curl = curl_init();
            curl_setopt( $curl, CURLOPT_URL, "https://clients6.google.com/rpc" );
            curl_setopt( $curl, CURLOPT_POST, true );
            curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
            curl_setopt( $curl, CURLOPT_POSTFIELDS, '[{"method":"pos.plusones.get","id":"p","params":{"nolog":true,"id":"'.rawurldecode( $this->url ).'","source":"widget","userId":"@viewer","groupId":"@self"},"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]' );
            curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
            curl_setopt( $curl, CURLOPT_HTTPHEADER, array('Content-type: application/json') );
            $curl_results = curl_exec ($curl);
            curl_close ($curl);
            $json = json_decode($curl_results, true);
            $this->store_transient("plusones");

            return isset( $json[0]['result']['metadata']['globalCounts']['count'] ) ? intval( $json[0]['result']['metadata']['globalCounts']['count'] ) : 0;
        }
    }

    /**
     * Gets Pinterest Share count
     * @return int Share Count Number
     */
    function get_pinterest() {
        if( $this->is_transient("pinterest") ){
            $data = $this->dbs_get_transient("pinterest");

            return isset( $data['count'] ) ? intval( $data['count'] ) : 0;
        } else {
            $return_data = $this->file_get_contents_curl('http://api.pinterest.com/v1/urls/count.json?url='.$this->url);
            $json_string = preg_replace('/^receiveCount((.*))$/', "\1", $return_data);
            $json = json_decode( $json_string, true );
            $this->store_transient("pinterest", $json);

            return isset( $json['count'] ) ? intval( $json['count'] ) : 0;
        }
    }

    /**
     * Stores social count data using WP_Transients. Sets cache for $timeout * Hours.
     * @param  "String" $social_platform Social media platform reference
     * @param  "String" $data            Social count data
     */
    private function store_transient( $social_platform, $data ) {
        $url_platform = $url . $social_platform;
        $trans_url = get_transient( $url_platform );
        set_transient( $url_platform, $data , $timeout * HOUR_IN_SECONDS );
    }

    /**
     * Checks to see if a certain WP_Transient exists.
     * @param  String  $social_platform Social media platform reference
     * @return boolean					True if the transient exists.
     */
    private function is_transient( $social_platform ) {
        $url_platform = $url . $social_platform;
        $trans_url = get_transient( $url_platform );
        return ( false === $trans_url ) ? false : true;
    }

    /**
     * Pulls the transient data
     * @param  String  $social_platform Social media platform reference
     * @return array                Transient Data
     */
    private function dbs_get_transient( $social_platform ){
        $url_platform = $url . $social_platform;
        return get_transient( $url_platform );
    }

    /**
     * Initiates an HTTP request for information
     * @param  String $url The request url
     * @return String 	   The response data      
     */
    private function file_get_contents_curl( $url ){
        $ch = curl_init();
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT'] );
        curl_setopt( $ch, CURLOPT_FAILONERROR, 1 );
        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_TIMEOUT, 8 ); // Timeout after 8 seconds. Prevents your page from white screen of terror.
        $cont = curl_exec( $ch );

        if( curl_error( $ch ) ) {
            die( curl_error($ch) );
        }
        
        return $cont;
    }

}
