<?php
        
/*              
        Plugin Name: Bookmarks
        Plugin URI: https://github.com/NoahY/q2a-bookmarks
        Plugin Description: Bookmarking
        Plugin Version: 1.0b
        Plugin Date: 2011-09-11
        Plugin Author: NoahY
        Plugin Author URI:                              
        Plugin License: GPLv2                           
        Plugin Minimum Question2Answer Version: 1.4
*/                      
                        
        
        if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
                        header('Location: ../../');
                        exit;   
        }               
  
        qa_register_plugin_module('module', 'qa-bookmarks-admin.php', 'qa_bookmarks_admin', 'Bookmarks Admin');
        
        qa_register_plugin_layer('qa-bookmarks-layer.php', 'Bookmarks Layer');

                        
/*                              
        Omit PHP closing tag to help avoid accidental output
*/                              
                          

