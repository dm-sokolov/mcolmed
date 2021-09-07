$(function () {

    // Initialize the jQuery File Upload widget:
    $('#fileupload').fileupload({
        // Uncomment the following to send cross-domain cookies:
        //xhrFields: {withCredentials: true},
        url: '/admin/multiload/jquery-upload/'
    });
	
	// Инфогруппа
	$("#infsysgroupselect").change(function(){
		$.get("/admin/multiload/action/get_items.php", {'infsysgroup': $("#infsysgroupselect option:selected").val(), 'infsysid': $("#infsysselect option:selected").val()},
		function(data){
			$("#infsysitemselect").html(data);
		});
	});
	
	// Инфосистема
	$("#infsysselect").change(function(){
		$.get("/admin/multiload/action/get_groups.php", {'infsysid': $("#infsysselect option:selected").val()},
		function(data){
			$("#infsysgroupselect").html(data);
			$("#infsysgroupselect").change();
		});
		$.get("/admin/multiload/action/get_properties.php", {'infsysid': $("#infsysselect option:selected").val()},
		function(data){
			$("#infsyspropselect").html(data);
		});		
	});
	
	// Магазин
	$("#shopselect").change(function(){
		$.get("/admin/multiload/action/get_groups.php", {'shopid': $("#shopselect option:selected").val()},
		function(data){
			$("#shopgroupselect").html(data);
			$("#shopgroupselect").change();
		});
	});
	
	// Раздел магазина
	$("#shopgroupselect").change(function(){
		$.get("/admin/multiload/action/get_items.php", {'shopgroup': $("#shopgroupselect option:selected").val(), 'shopid': $("#shopselect option:selected").val()},
			function(data){
				$("#shopitemselect").html(data);
		});
		
		$.get("/admin/multiload/action/get_properties.php", {'shopid': $("#shopselect option:selected").val(), 'shopgroup': $("#shopgroupselect option:selected").val()},
			function(data){
				$("#shoppropselect").html(data);
		});
		
	});
	
	$("#id_tab_span_0, #id_tab_span_1").click(function(){
			$('#uploadify').uploadifyDestroy();
			InitUploadifyQueue();
		}
	);
	
	$("#type_selector input").change(function(){
		
		if($(this).val() == 0)
		{
			// Загрузка в элемент инофсистемы
			$("#infsysitemselect_div").css("display", "none");
			$("#infsyspropselect_div").css("display", "none");
			
			$("#infsysitemselect").val(0);
			$("#infsyspropselect").val(0);
		}
		else 
		{
			// Загрузка в доп. свойство элемента инофсистемы
			$("#infsysitemselect_div").css("display", "block");
			$("#infsyspropselect_div").css("display", "block");
		}
		
	});
	
	// Вкладка Информационная система
	$("#id_content-tab-0").click(function(){
		$("#loadtype").val(1);
	});		
	
	// Вкладка Интернет-магазин
	$("#id_content-tab-1").click(function(){
		$("#loadtype").val(2);
	});

	// $('#fileupload').fileupload({
		// dataType: 'json',
		// add: function (e, data) {            
			// $("#upload-button").off('click').on('click', function () {
				// console.log(111);
				// data.submit();
			// });
		// },
	// });
	// $('#fileupload').fileupload({
		// dataType: 'json',
		// add: function (e, data) {
				// if (data.autoUpload || (data.autoUpload !== false &&
						// $(this).fileupload('option', 'autoUpload'))) {
					// data.process().done(function () {
						// data.submit();
					// });
				// }
			// }
		// },
	// });
});

function isCanUpload()
{
	var loadFlag = false;
			
	$type = $("#loadtype").val();	
	
	if ($type == 1)
	{
		if ($("#infsysselect option:selected").val() != 0)
		{
			loadFlag = true;
		}
	} else
	{
		if ($("#shopselect option:selected").val() != 0 && $("#shoppropselect option:selected").val() != 0 && $("#shopitemselect option:selected").val() != 0)
		{
			loadFlag = true;
		}			
	}
	
	return loadFlag;
}
