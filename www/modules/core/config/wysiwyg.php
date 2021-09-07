<?php

return array(
	'theme' => '"silver"',
	'plugins' => '[\'advlist autolink lists link image charmap print preview hr anchor pagebreak\', \'searchreplace wordcount visualblocks visualchars code fullscreen, insertdatetime media nonbreaking save table directionality\', \'emoticons template paste textpattern imagetools codesample toc importcss\']',
	'toolbar1' => '"undo redo | styleselect fontselect fontsizeselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link unlink image media code preview"',
	'toolbar2' => '"table | cut copy paste | forecolor backcolor | hr removeformat | subscript superscript | pagebreak codesample emoticons"',
	'image_advtab' => 'true',
	'menubar' => '"edit insert format view table"',
	'toolbar_items_size' => '"small"',
	'insertdatetime_dateformat' => '"%d.%m.%Y"',
	'insertdatetime_formats' => '["%d.%m.%Y", "%H:%M:%S"]',
	'insertdatetime_timeformat' => '"%H:%M:%S"',
	'valid_elements' => '"*[*],i[*]"',
	'extended_valid_elements' => '"*[*],noindex[*]"',
	'convert_urls' => 'false',
	'relative_urls' => 'false',
	'remove_script_host' => 'false',
	'forced_root_block' => '""',
	'entity_encoding' => '""',
	'verify_html' => 'false',
	'valid_children' => '"+body[style]"',
	'force_p_newlines' => 'true',
	'browser_spellcheck' => 'true',
	'content_css' => '/css/bootstrap.css',
	'file_picker_callback' => 'function (callback, value, meta) { HostCMSFileManager.fileBrowserCallBack(callback, value, meta) }'
);