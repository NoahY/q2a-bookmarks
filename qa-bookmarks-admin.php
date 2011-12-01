<?php
	class qa_bookmarks_admin {

		function option_default($option) {
			
			switch($option) {
				case 'bookmarks_plugin_title':
					return 'Bookmarks';
				case 'bookmarks_plugin_bookmark':
					return 'Bookmark this question';
				case 'bookmarks_plugin_unbookmark':
					return 'Unbookmark this question';
				case 'ajax_bookmark_popup_notice_text':
					return 'Question bookmarked.&nbsp; Visit your profile to see bookmarked questions.';
				case 'ajax_bookmark_popup_un_notice_text':
					return 'Bookmark removed.';
				case 'badges/bookmarker':
					return 'Bookmarker';
				case 'badges/bookkeeper':
					return 'Bookkeeper';
				case 'badges/bookworm':
					return 'Bookworm';
				case 'badges/bookmarker_desc':
				case 'badges/bookkeeper_desc':
				case 'badges/bookworm_desc':
					return 'Bookmarked # ^post^posts';
				default:
					return null;				
			}
			
		}
		
		function custom_badges() {
			return array(
				'bookmarker' => array('var'=>1, 'type'=>0),
				'bookkeeper' => array('var'=>8, 'type'=>1),
				'bookworm' => array('var'=>20, 'type'=>2)
			);
		}
		
		
		function custom_badges_rebuild() {
			$awarded = 0;
			
			$userq = qa_db_query_sub(
				'SELECT user_id, meta_value FROM ^usermeta WHERE meta_key=$',
				'max_bookmarks'
			);
			while ( ($user=qa_db_read_one_assoc($userq,true)) !== null ) {
				$badges = array('bookmarker','bookkeeper','bookworm');
				$awarded += count(qa_badge_award_check($badges,(int)$user['meta_value'],$user['user_id'],null,2));
			}
			return $awarded;
		}
		
		function allow_template($template)
		{
			return ($template!='admin');
		}	   
			
		function admin_form(&$qa_content)
		{					   
							
		// Process form input
			
			$ok = null;
			
			if (qa_clicked('bookmarks_plugin_save')) {
				qa_db_query_sub(
					'CREATE TABLE IF NOT EXISTS ^usermeta (
					meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					user_id bigint(20) unsigned NOT NULL,
					meta_key varchar(255) DEFAULT NULL,
					meta_value longtext,
					PRIMARY KEY (meta_id),
					UNIQUE (user_id,meta_key)
					) ENGINE=MyISAM  DEFAULT CHARSET=utf8'
				);		
				qa_opt('bookmarks_plugin_enable',(bool)qa_post_text('bookmarks_plugin_enable'));
				qa_opt('bookmarks_plugin_title',qa_post_text('bookmarks_plugin_title'));
				qa_opt('bookmarks_plugin_bookmark',qa_post_text('bookmarks_plugin_bookmark'));
				qa_opt('bookmarks_plugin_unbookmark',qa_post_text('bookmarks_plugin_unbookmark'));
				qa_opt('ajax_bookmark_popup_notice_text',qa_post_text('ajax_bookmark_popup_notice_text'));
				qa_opt('ajax_bookmark_popup_un_notice_text',qa_post_text('ajax_bookmark_popup_un_notice_text'));
				qa_opt('badges/bookmarker',qa_post_text('badges/bookmarker'));
				qa_opt('badges/bookmarker_desc',qa_post_text('badges/bookmarker_desc'));
				qa_opt('badges/bookkeeper',qa_post_text('badges/bookkeeper'));
				qa_opt('badges/bookkeeper_desc',qa_post_text('badges/bookkeeper_desc'));
				qa_opt('badges/bookworm',qa_post_text('badges/bookworm'));
				qa_opt('badges/bookworm_desc',qa_post_text('badges/bookworm_desc'));
				$ok = qa_lang_html('admin/options_saved');
			}
			
					
			// Create the form for display
			
			$fields = array();
			
			$fields[] = array(
				'label' => 'Enable bookmarking',
				'tags' => 'NAME="bookmarks_plugin_enable"',
				'value' => qa_opt('bookmarks_plugin_enable'),
				'type' => 'checkbox',
			);
				
			$fields[] = array(
				'label' => 'Bookmark list title',
				'type' => 'text',
				'value' => qa_html(qa_opt('bookmarks_plugin_title')),
				'tags' => 'NAME="bookmarks_plugin_title"',
			);		   
			
				
			$fields[] = array(
				'label' => 'Bookmark hover text',
				'type' => 'text',
				'value' => qa_html(qa_opt('bookmarks_plugin_bookmark')),
				'tags' => 'NAME="bookmarks_plugin_bookmark"',
			);		   
		  
			$fields[] = array(
				'label' => 'Unbookmark hover text',
				'type' => 'text',
				'value' => qa_html(qa_opt('bookmarks_plugin_unbookmark')),
				'tags' => 'NAME="bookmarks_plugin_unbookmark"',
			);		   

				
			$fields[] = array(
				'label' => 'Bookmark notice text',
				'type' => 'text',
				'value' => qa_html(qa_opt('ajax_bookmark_popup_notice_text')),
				'tags' => 'NAME="ajax_bookmark_popup_notice_text"',
			);		   

				
			$fields[] = array(
				'label' => 'Unbookmark notice text',
				'type' => 'text',
				'value' => qa_html(qa_opt('ajax_bookmark_popup_un_notice_text')),
				'tags' => 'NAME="ajax_bookmark_popup_un_notice_text"',
			);		   
			
			$fields[] = array(
				'type' => 'blank',
			);		   
			
			$fields[] = array(
				'label' => 'Bronze badge name',
				'type' => 'text',
				'value' => qa_opt('badges/bookmarker'),
				'tags' => 'NAME="badges/bookmarker"',
			);		   

				
			$fields[] = array(
				'label' => 'Bronze badge description',
				'type' => 'text',
				'value' => qa_opt('badges/bookmarker_desc'),
				'tags' => 'NAME="badges/bookmarker_desc"',
			);		   
		  		  		  
			$fields[] = array(
				'label' => 'Silver badge name',
				'type' => 'text',
				'value' => qa_opt('badges/bookkeeper'),
				'tags' => 'NAME="badges/bookkeeper"',
			);		   

				
			$fields[] = array(
				'label' => 'Silver badge description',
				'type' => 'text',
				'value' => qa_opt('badges/bookkeeper_desc'),
				'tags' => 'NAME="badges/bookkeeper_desc"',
			);	   
		  		  		  
			$fields[] = array(
				'label' => 'Gold badge title',
				'type' => 'text',
				'value' => qa_opt('badges/bookworm'),
				'tags' => 'NAME="badges/bookworm"',
			);		   

				
			$fields[] = array(
				'label' => 'Gold badge description',
				'type' => 'text',
				'value' => qa_opt('badges/bookworm_desc'),
				'tags' => 'NAME="badges/bookworm_desc"',
			);		   
		  		  		  

			return array(		   
				'ok' => ($ok && !isset($error)) ? $ok : null,
					
				'fields' => $fields,
			 
				'buttons' => array(
					array(
						'label' => 'Save',
						'tags' => 'NAME="bookmarks_plugin_save"',
					)
				),
			);
		}
	}

