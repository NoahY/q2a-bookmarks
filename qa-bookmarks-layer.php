<?php

	class qa_html_theme_layer extends qa_html_theme_base {

	// check for post
		
		function doctype()
		{
			if(!isset($_POST['ajax_bookmark_qid'])) qa_html_theme_base::doctype();
		}

		function html()
		{
			
			if(isset($_POST['ajax_bookmark_qid'])) $this->ajaxBookmark(qa_post_text('ajax_bookmark_qid'),qa_post_text('ajax_bookmark_uid'),qa_post_text('ajax_bookmarked'));
			else qa_html_theme_base::html();
		}
		
	// theme replacement functions
	
		function head_custom()
		{
			if (qa_opt('bookmarks_plugin_enable') && $this->template == 'question') {
				$this->output_raw('
				<style>
					#bookmark:hover {
						background-image:url('.QA_HTML_THEME_LAYER_URLTOROOT.'onbookmark.png);
					}
					#bookmark {
						cursor: pointer;
						float: right;
						margin: 12px 12px 12px 0;
						width: 24px;
						height: 24px;
						background-size:;
					}
					.bookmark {
						background-image:url('.QA_HTML_THEME_LAYER_URLTOROOT.'bookmark.png);
					}
					.unbookmark {
						background-image:url('.QA_HTML_THEME_LAYER_URLTOROOT.'unbookmark.png);
					}
					.ajax-bookmark-popup {
						left: 0;
						right: 0;
						top: 0;
						padding: 0;
						position: fixed;
						width: 100%;
						z-index: 10000;
						cursor:pointer;
						display:none;
					}
					.ajax-bookmark-popup-text {
						background-color: #F6DF30;
						color: #444444;
						font-weight: bold;
						width: 100%;
						text-align: center;
						font-family: sans-serif;
						font-size: 14px;
						padding: 10px 0;
						position:relative;
					}
				</style>');
				$this->output_raw("
				<script>
					function ajaxBookmarkConfirm() {
						jQuery('<div class=\"ajax-bookmark-popup\"><div class=\"ajax-bookmark-popup-text\" onclick=\"this.style.display=\\'none\\';\">".qa_html(qa_opt('ajax_bookmark_popup_notice_text'))."</div></div>').insertAfter(jQuery('#bookmark')).fadeIn('fast').delay(5000).fadeOut('slow');
					}
					function ajaxBookmark(qid,uid,bmd) {
						var dataString = 'ajax_bookmark_qid='+qid+'&ajax_bookmark_uid='+uid+'&ajax_bookmarked='+bmd;
						jQuery.ajax({  
							type: 'POST',  
							url: '".qa_self_html()."',  
							data: dataString,  
							success: function(data) {
								data = data.substring(1);
								if(/^###/.exec(data)) {
									var error = data.substring(4);
									window.alert(error);
								}
								else{
									jQuery('#bookmark').replaceWith(data);
									if(!bmd) ajaxBookmarkConfirm();
								}  
							}
						});
					}
				</script>");					
			}
			qa_html_theme_base::head_custom();
		}
		function page_title()
		{
			if(qa_opt('bookmarks_plugin_enable') && $this->template == 'question') {
				$this->bookmark($this->content['q_view']['raw']['postid']);
			}
			qa_html_theme_base::page_title();
		}

		function main_parts($content)
		{
			if (qa_opt('bookmarks_plugin_enable') && $this->template == 'user') {

				if($content['q_list']) {  // paranoia
				
					$keys = array_keys($content);
					$vals = array_values($content);

					$insertBefore = array_search('q_list', $keys);

					$keys2 = array_splice($keys, $insertBefore);
					$vals2 = array_splice($vals, $insertBefore);

					$keys[] = 'form-bookmarks-list';
					$vals[] = $this->bookmarks_plugin_form();

					$content = array_merge(array_combine($keys, $vals), array_combine($keys2, $vals2));
				}
				else $content['form-bookmarks-list'] = $this->bookmarks_plugin_form();  // this shouldn't happen
					
			}
			qa_error_log($content);

			qa_html_theme_base::main_parts($content);

		}
	
	// worker functions
		function getuserfromhandle($handle) {
			require_once QA_INCLUDE_DIR.'qa-app-users.php';
			
			if (QA_FINAL_EXTERNAL_USERS) {
				$publictouserid=qa_get_userids_from_public(array($handle));
				$userid=@$publictouserid[$handle];
				
			} 
			else {
				$userid = qa_db_read_one_value(
					qa_db_query_sub(
						'SELECT userid FROM ^users WHERE handle = $',
						$handle
					),
					true
				);
			}
			if (!isset($userid)) return;
			return $userid;
		}

		function bookmarks_plugin_form() {
			// displays bookmarks_plugin_form form in user profile
			
			global $qa_request;
			
			$handle = preg_replace('/^[^\/]+\/([^\/]+).*/',"$1",$qa_request);
			
			$uid = $this->getuserfromhandle($handle);
			
			if(!$uid) return;
			if(qa_get_logged_in_handle() && qa_get_logged_in_handle() == $handle) {

				$bookmarks = $this->get_bookmarks_for_user($uid);
				if(!$bookmarks) return;
				
				
				$output = '<div class="bookmarks-container">';
				$query = qa_db_query_sub(
					'SELECT title,postid FROM ^posts WHERE type=$ AND postid in ('.$bookmarks.')',
					'Q'
				);
				while ( ($post=qa_db_read_one_assoc($query,true)) !== null ) {
					
					$title=$post['title'];
					$oid=$post['postid'];
					
					$length = 60;
					
					$text = (strlen($title) > $length ? substr($title,0,$length).'...' : $title);
					
					$output .= '<div class="bookmark-row"><a href="'.qa_path_html(qa_q_request($oid,$title),NULL,qa_opt('site_url')).'">'.$text.'</a></div>';
				}
				
				$fields['bookmarks'] = array(
					'type' => 'static',
					'label' => $output,
				);


				$form=array(
					'style' => 'tall',
					
					'title' => qa_opt('bookmarks_plugin_title'),

					'fields' => $fields,
				);
				return $form;
			}			
		}

		function bookmark($qid,$uid = null,$bookmarked='check') {
			if(!$uid) $uid = qa_get_logged_in_userid();
			if(!$uid) return;
			if($bookmarked == 'check') {
				$bookmarks = $this->get_bookmarks_for_user($uid);
				$bookmarked = false;
				if(strpos(','.$bookmarks.',',','.$qid.',') !== false) {
					$bookmarked = true;
				}
			}
			$this->output_raw('<DIV onclick="ajaxBookmark('.$qid.','.$uid.','.($bookmarked?'true':'false').')" title="'.qa_opt('bookmarks_plugin_'.($bookmarked?'un':'').'bookmark').'" id="bookmark" class="'.($bookmarked?'un':'').'bookmark"></DIV>');
		}
		
		function get_bookmarks_for_user($uid) {
			$bookmarks = qa_db_read_one_value(
				qa_db_query_sub(
					'SELECT meta_value FROM ^usermeta WHERE user_id=# AND meta_key=$',
					$uid, 'bookmarks'
				),true
			);
			return $bookmarks;
		}
		
		function ajaxBookmark($qid,$uid,$bmd) {
			eval('$bmd = '.$bmd.';');
			$bookmarks = $this->get_bookmarks_for_user($uid);
			if($bookmarks) {
				if(!$bmd) {
					$bookmarks = $bookmarks.','.$qid;
					qa_db_query_sub(
						'UPDATE ^usermeta SET meta_value=$ WHERE user_id=# AND meta_key=$',
						$bookmarks,$uid,'bookmarks'
					);
				}
				else {
					$bookmarks = substr(str_replace(','.$qid.',',',',','.$bookmarks.','),1,-1);
					qa_db_query_sub(
						'UPDATE ^usermeta SET meta_value=$ WHERE user_id=# AND meta_key=$',
						$bookmarks,$uid,'bookmarks'
					);
				}
			}
			else {
				if(!$bmd) {
					qa_db_query_sub(
						'INSERT INTO ^usermeta (user_id,meta_key,meta_value) VALUES (#,$,$) ON DUPLICATE KEY UPDATE meta_value=$',
						$uid,'bookmarks',$qid,$qid
					);
				}
				else {
					$bookmarks = substr(str_replace(','.$qid.',',',',$bookmarks),1,-1);
					qa_db_query_sub(
						'DELETE FROM ^usermeta WHERE user_id=# AND meta_key=$',
						$uid,'bookmarks'
					);
				}
			}
			$this->bookmark($qid,$uid,($bmd==false));
		}
	}

