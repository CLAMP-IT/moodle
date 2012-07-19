<?php // $Id$

$string['pluginname'] = 'Marginalia';
$string['splash'] = 'This is the annotation margin.  See the annotation drop down at the top of the page for help.  Click x to remove this message.';
$string['create_margin'] = 'Select text and click here or type Enter to create a new annotation.';
$string['summary_link'] = 'Annotation Summary';
$string['summary_link_title'] = 'Go to your annotation summary';
$string['sheet_private'] = 'My Private Annotations';
$string['sheet_none'] = 'Hide Annotations';
$string['sheet_public'] = 'Shared Annotations';
$string['summary_title'] = 'Annotation Summary';
$string['quote_button'] = 'Quote';
$string['prompt_find'] = 'Find';
$string['prompt_by'] = 'by';
$string['search_of_all'] = 'all annotations';
$string['search_of_self'] = 'annotations of my work';
$string['search_by_all'] = 'anyone';
$string['search_by_self'] = 'myself';
$string['search_by_teachers'] = 'teachers';
$string['search_by_students'] = 'students';
$string['search_text'] = 'containing';
$string['summary_range_error'] =
	'You have been directed here because one or more annotations could not'
	. ' be displayed.  This is probably because the annotated text changed.'
	. ' This summary includes the annotations which could not be shown.';
$string['prompt_search_desc'] = 'Showing {$a->n} of {$a->m}';
$string['prompt_section'] = 'Go to {$a->section_type}';
$string['prompt_row'] = 'Go to {$a->row_type} by {$a->author}';
$string['private'] = 'private';
$string['public'] = 'public';
$string['author'] = 'author';
$string['teacher'] = 'teacher';
$string['author+teacher'] = 'both';
$string['atom_feed'] = 'Atom 1.0 Feed';
$string['atom_feed_desc'] = 'Subscribe to updates to this page (only recent public annotations will be included).';
$string['unknown_course'] = 'unknown course';
$string['all_discussions'] = 'all discussions';
$string['discussion_name'] = 'discussion &quot;{$a->name}&quot;';
$string['forum_name'] = 'forum &quot;{$a->name}&quot;';
$string['unknown_discussion'] = 'unknown discussion';
$string['whole_course'] = 'whole course';
$string['unknown_post'] = 'unknown discussion post';
$string['post_name'] = 'discussion post "{$a->name}"';
$string['annotation_help'] = 'creating and using annotations';
$string['annotate_help_link'] = 'Annotation Help...';
$string['annotation_summary_help_link'] = 'How to use this page';
$string['missing_help'] = 'No help for topic.';

/* Summary page */
$string['containing'] = 'text containing';
$string['matching'] = 'notes matching';
$string['annotation_desc_authorsearch'] = 'annotations by {$a->who} of work by {$a->author} with {$a->match} &quot;{$a->search}&quot; in {$a->title}';
$string['annotation_desc_author'] = 'annotations by {$a->who} of work by {$a->author} in {$a->title}';
$string['annotation_desc_search'] = 'annotations by {$a->who} with {$a->match} &quot;{$a->search}&quot; in {$a->title}';
$string['annotation_desc'] = 'annotations by {$a->who} in {$a->title}';
$string['tip'] = 'Tip';
$string['smartcopy_help'] = 'The Smartcopy feature automatically includes context information'
	. ' when you copy and paste text from a blog post.  To switch it on or off, press'
	. ' Shift-Ctrl-S while viewing a discussion forum.';
$string['source_th'] = 'Source';
$string['quote_th'] = 'Highlighted Text';
$string['note_th'] = 'Margin Note';
$string['user_th'] = 'User';
$string['anyone'] = 'anyone';
$string['me'] = 'Me';
$string['zoom_user_hover'] = 'Click to show only annotations by {$a->fullname}.';
$string['zoom_author_hover'] = 'Click to show only annotations of work by {$a->fullname}.';
$string['zoom_url_hover'] = 'Click to show only annotations in this {$a->section_type}.';
$string['zoom_match_hover'] = 'Click to show only notes matching this exact text.';
$string['unzoom_user_hover'] = 'Click to view annotations by anyone.';
$string['unzoom_author_hover'] = 'Click to view annotations of any user&#38;s work.';
$string['unzoom_url_hover'] = 'Click to broaden the search.';
$string['unzoom_match_hover'] = 'Click to include all occurrences of this text.';
$string['smartquote_annotation'] = 'Quote this annotation in a forum post.';

$string['annotation_summary'] = 'using the annotation summary.';
$string['annotation_summary_help'] = 'what for?';
$string['summary_help'] = 'using the annotation summary.';
$string['summary_help_help'] = 'uh... whats this for?';

$string['summary_sort_document'] = "Show annotations in document order.";
$string['summary_sort_time'] = "Show most recent annotations first.";
$string['summary_source_head'] = 'Source';
$string['summary_quote_head'] = 'Highlighted Text';
$string['summary_note_head'] = 'Margin Note';
$string['summary_time_head'] = 'Modified';
$string['summary_user_head'] = 'User';

/* Edit Keywords Page */
$string['edit_keywords_link' ] = 'Annotation Tags';
$string['edit_keywords_title'] = 'Annotation Tags';
$string['keyword_column'] = 'Tag';
$string['keyword_desc_column'] = 'Description';
$string['create_keyword_button'] = 'Create Tag';
$string['note_replace_legend'] = 'Search and Replace Margin Notes';
$string['note_replace_old'] = 'Existing note text';
$string['note_replace_new'] = 'Replacement note text';
$string['note_replace_button'] = 'Replace Notes';
$string['note_update_count'] = 'Notes updated: ';
$string['tag_list_prompt'] = 'You have used the following notes multiple times.  Click on a link to view a summary of annotations with that note.';

/* Strings used in JS front-end */
$string['js_public_annotation'] = 'This annotation is public.';
$string['js_private_annotation'] =  'This annotation is private.';
$string['js_delete_annotation_button'] = 'Delete this annotation.';
$string['js_annotation_link_button'] = 'Link to another document.';
$string['js_annotation_link_label'] = 'Select a document to link to.';
$string['js_delete_annotation_link_button'] = 'Remove this link.';
$string['js_annotation_expand_edit_button'] = 'Click to display margin note editor';
$string['js_annotation_collapse_edit_button'] = 'Click to display margin note drop-down list';
$string['js_annotation_quote_button'] = 'Quote this annotation in a discussion post.';
$string['js_chars_remaining'] = 'characters remaining';
$string['js_edit_annotation_click'] = 'Click to edit the text of this annotation.';
$string['js_note_user_recent_title'] = 'Note recently modified on: ';
$string['js_note_user_title'] = 'Note last modified on: ';
$string['js_delete_tip_button'] = 'Remove this message.';

$string['js_browser_support_of_W3C_range_required_for_annotation_creation'] = 'Your browser does not support the W3C range standard, so you cannot create annotations.';
$string['js_select_text_to_annotate'] = 'You must select some text to annotate.';
$string['js_invalid_selection'] = 'Selection range is not valid.';
$string['js_corrupt_XML_from_service'] = 'An attempt to retrieve annotations from the server returned corrupt XML data.';
$string['js_note_too_long'] = 'Please limit your margin note to 250 characters.';
$string['js_quote_too_long'] = 'The passage you have attempted to highlight is too long.  It may not exceed 1000 characters.';
$string['js_zero_length_quote'] = 'You must select some text to annotate.';
$string['js_quote_not_found'] = 'The highlighted passage could not be found';
$string['js_create_overlapping_edits'] = 'You may not create overlapping edits';

$string['marginalia:view_all'] = 'View all';
$string['marginalia:fix_notes'] = 'Fix notes';