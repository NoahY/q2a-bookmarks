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
				default:
					return null;				
			}
			
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
				if(!qa_opt('bookmarks_plugin_enable') && qa_post_text('bookmarks_plugin_enable')) {
					$table_exists = qa_db_read_one_value(qa_db_query_sub("SHOW TABLES LIKE '^usermeta'"),true);
					if(!$table_exists) {
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
					}
				}
				qa_opt('bookmarks_plugin_enable',(bool)qa_post_text('bookmarks_plugin_enable'));
				qa_opt('bookmarks_plugin_title',qa_post_text('bookmarks_plugin_title'));
				qa_opt('bookmarks_plugin_bookmark',qa_post_text('bookmarks_plugin_bookmark'));
				qa_opt('bookmarks_plugin_unbookmark',qa_post_text('bookmarks_plugin_unbookmark'));
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
				'label' => 'Bookmark notice text',
				'type' => 'text',
				'value' => qa_html(qa_opt('ajax_bookmark_popup_notice_text')),
				'tags' => 'NAME="ajax_bookmark_popup_notice_text"',
			);		   
		  
				
			$fields[] = array(
				'label' => 'Unbookmark hover text',
				'type' => 'text',
				'value' => qa_html(qa_opt('bookmarks_plugin_unbookmark')),
				'tags' => 'NAME="bookmarks_plugin_unbookmark"',
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

