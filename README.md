##Social Media Share Counts
###Cool stuff from DBS>Interactive


####Quick Use

Include dbs_share_count.php in your functions file.

```php

require_once("dbs_share_count.php");

```

Instantiate the class on the template you want to display your share count data on.

```php

$options = array(
	"share_url" => WP_SITEURL . $_SERVER['REQUEST_URI'], // Default - Required
	"share_title" => get_the_title() . " at @the_most_awesome_company", // Optional
	"share_text" => "Check out " . get_the_title() . " @the_most_awesome_company", // Optional
	"twitter_summary" => "Check out " . get_the_title() . " @the_most_awesome_company", // Optional
	"media_url" => $share_media, // Optional
	"timeout" => 4 // Optional
);

$sharecount = new DBSShareCount( $options );

```


Add this to your template file.

```php

<li class="facebook">
     <a href="<?php echo $sharecount->get_facebook_url(); ?>" title="Share on Facebook">
       Like <span class="count"><?php echo $sharecount->get_fb_likes(); ?></span>
   </a>
</li>

```