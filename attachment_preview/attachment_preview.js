/* Drag upload plugin script */
if (window.rcmail) {

	rcmail.addEventListener('init', function(evt) {
			$(document).ready(function() {
				if (rcmail.env.attachments)
				{

				var list = rcmail.gui_objects.attachmentlist.getElementsByTagName("li");
				for (i=0; i<list.length; i++){

				var url = rcmail.env.comm_path+'&_action=display-attachment&_file='+list[i].id+'&_id='+rcmail.env.compose_id;
				list[i].innerHTML = list[i].innerHTML.replace(/\<\/a\>/, '</a><a href="'+url+'">');
				begin=rcmail.gui_objects.attachmentlist.getElementsByTagName("a");
				list[i].innerHTML += '</a>';

				}

				}
				});
			})
}

