<?php
/*
Plugin Name: Slack Notification
Description: A Slack Notification that notifies number of new and updated posts from yesterday for each posts and for each users
Version:     1.0
Author:      Vindecodex
*/


add_action("rest_api_init", function() {
  register_rest_route("_", "/notify_slack", array(
    "methods" => "GET",
    "callback" => "send_notification",
     ));
});


function send_notification($request) {
  $access_token ="YOU_ACCESS_TOKEN";
  $webhook_endpoint = "YOUR_WEBHOOK_ENDPOINT";

  if ($request->get_header("X-ACCESS-TOKEN") == $access_token):
    if($request->get_header("TEST") == true):
      $webhook_endpoint = "YOUR_TEST_WEBHOOK_ENDPOINT";
    endif;

  $data_encoded = array(
    "payload" => json_encode(create_message())
  );

    $post_on_slack = wp_remote_post($webhook_endpoint, array(
      'method' => 'POST',
      'headers' => array(),
      'body' => $data_encoded,
    )
  );
  return "Slack Notification Sent";
  else:
  return "Error Sending Notification";
  endif;
}

function create_message() {

  $pretext = "*Yesterday's updates @cont @write*";
  $yesterday_date = date("Y年m月d日",strtotime("-1 days"));

  // List of Sites that will not be notified
  $block_sites = array(
    "@wiki",
    "test"
  );


  $data = array(
    "text" => "Daily Data Notification",
    "blocks" => array(
      0 => array(
        "type" => "section",
        "text" => array(
          "type" => "mrkdwn",
          "text" => $pretext . "\n" . "Date: " . $yesterday_date. "\n" . 
          "Total:" .  " New " . get_overall_total_article($block_sites) .  " Article/s" . "  Updated " . get_overall_total_updatedarticle($block_sites) . " Article/s"
      )
    )
)
);


   foreach( get_sites() as $site ) {

     switch_to_blog($site->blog_id);

     if( !is_site_blocklisted($block_sites, get_bloginfo('name')) ):

     array_push($data["blocks"],array(
       "type" => "divider"
     ));

    array_push($data["blocks"],array(
      "type" => "section",
      "text" => array(
        "type" => "mrkdwn",
        "text" => "Site name: <". get_site_url() . "|" . get_bloginfo('name') . ">\n" .
                  "Total : New " . new_post_count_yesterday() . " articles \t\t Updates " . updated_post_count_yesterday() . " articles"
                )
              ));

     foreach( getUsers() as $user ) {
       if(new_post_count_yesterday_by_user( $user->ID ) > 0 || updated_post_count_yesterday_by_user( $user->ID ) > 0):

       array_push($data["blocks"],array(
         "type" => "context",
         "elements" => array(
           0 => array(
             "type" => "mrkdwn",
             "text" => "Writer Name: " . $user->user_login . "\t New " . new_post_count_yesterday_by_user($user->ID) . " articles \t Updates " . updated_post_count_yesterday_by_user($user->ID) . " articles"
           )
       )
       ));

       endif;

     }
     endif;
   }

  return $data;

}

// Required Datas
function new_post_count_yesterday($post_type = 'post') {
  global $wpdb;
  $numposts = $wpdb->get_var(
    $wpdb->prepare(
      "SELECT COUNT(ID) " .
      "FROM {$wpdb->posts} " .
      "WHERE " .
      "post_status='publish' " .
      "AND post_type= %s " .
      "AND post_date <= %s " .
      "AND post_date >= %s " ,
      $post_type, date('Y-m-d H:i:s', strtotime('-1 days')), date('Y-m-d H:i:s', strtotime('-2 days'))
    )
  );
  return $numposts;
}
function updated_post_count_yesterday($post_type = 'revision') {
  global $wpdb;
  $numposts = $wpdb->get_var(
    $wpdb->prepare(
      "SELECT COUNT(ID) " .
      "FROM {$wpdb->posts} " .
      "WHERE " .
      "post_status='inherit' " .
      "AND post_type = %s " .
      "AND post_modified <= %s " .
      "AND post_modified >= %s " .
      "AND post_date <> DATE_SUB(post_modified, INTERVAL 1 SECOND)" ,
      $post_type, date('Y-m-d H:i:s', strtotime('-1 days')), date('Y-m-d H:i:s', strtotime('-2 days'))
    )
  );
  $result = ($numposts - new_post_count_yesterday("post"));
  if($result <= 0): return 0; endif;
  return $result;
}
function new_post_count_yesterday_by_user($user) {
  global $wpdb;
  $numposts = $wpdb->get_var(
    $wpdb->prepare(
      "SELECT COUNT(ID) " .
      "FROM {$wpdb->posts} " .
      "WHERE " .
      "post_status='publish' " .
      "AND post_author= %s" .
      "AND post_type= 'post' " .
      "AND post_date <= %s " .
      "AND post_date >= %s " ,
      $user, date('Y-m-d H:i:s', strtotime('-1 days')), date('Y-m-d H:i:s', strtotime('-2 days'))
    )
  );
  return $numposts;
}
function updated_post_count_yesterday_by_user($user) {
  global $wpdb;
  $numposts = $wpdb->get_var(
    $wpdb->prepare(
      "SELECT COUNT(ID) " .
      "FROM {$wpdb->posts} " .
      "WHERE " .
      "post_status='inherit' " .
      "AND post_author= %s" .
      "AND post_type = 'revision' " .
      "AND post_modified <= %s " .
      "AND post_modified >= %s " .
      "AND post_date <> DATE_SUB(post_modified, INTERVAL 1 SECOND)" ,
      $user, date('Y-m-d H:i:s', strtotime('-1 days')), date('Y-m-d H:i:s', strtotime('-2 days'))
    )
  );
  $result = ($numposts - new_post_count_yesterday_by_user($user));
  if ($result <= 0): return 0; endif;
  return $result;
}
function getUsers() {
  global $wpdb;
  $users = $wpdb->get_results("SELECT * FROM {$wpdb->users}");
  return $users;
}
function is_site_blocklisted(array $site_list, $site_name) {
  foreach($site_list as $site){
    if(gettype(strpos(strtolower($site_name), $site)) != "boolean") {
      return true;
    }
  }
  return false;
}

function get_overall_total_article(array  $block_sites){
   $overall = 0;
    foreach(get_sites() as $site):
       switch_to_blog($site->blog_id); 
      if( !is_site_blocklisted($block_sites, get_bloginfo('name')) ):
          $overall = $overall + new_post_count_yesterday('post');
   endif;
endforeach;
return $overall;
}

function get_overall_total_updatedarticle(array $block_sites){
    $overallupdate = 0;
    foreach(get_sites() as $site):
        switch_to_blog($site->blog_id);
    if( !is_site_blocklisted($block_sites, get_bloginfo('name')) ): 
        $overallupdate = $overallupdate + updated_post_count_yesterday ('revision');
endif;
endforeach;
return $overallupdate;
}
