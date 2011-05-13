/* Drag upload plugin script */
/* TODO: Preview, Hover enlarge, Hover Menu, Click Preview Window, Ajax Thumbnail loading

 */
if (window.rcmail) {

	rcmail.addEventListener('init', function(evt) {
			$(document).ready(function() {
				if (rcmail.env.attachments)
				{

				var list = rcmail.gui_objects.attachmentlist.getElementsByTagName("li");
				for (i=0; i<list.length; i++){

				list[i].style.height="auto";
				var url = rcmail.env.comm_path+'&_action=display-attachment&_file='+list[i].id+'&_id='+rcmail.env.compose_id;
				var img_url = rcmail.env.comm_path+'&_action=plugin.preview&_file='+list[i].id+'&_id='+rcmail.env.compose_id;
				list[i].innerHTML = list[i].innerHTML.replace(/\<\/a\>/, '</a><a href="'+url+'">');
				begin=rcmail.gui_objects.attachmentlist.getElementsByTagName("a");
				list[i].innerHTML += '</a><div><img src="'+img_url+'"></div>';

				}

				}
				});
			})
	rcmail.ori_add2attachment_list=rcmail.add2attachment_list;
	rcmail.add2attachment_list = function(name, att, upload_id) {
		if(att.complete){
			var url = rcmail.env.comm_path+'&_action=display-attachment&_file='+name+'&_id='+rcmail.env.compose_id;
			var img_url = rcmail.env.comm_path+'&_action=plugin.preview&_file='+name+'&_id='+rcmail.env.compose_id;
			att.html = att.html.replace(/\<\/a\>/, '</a><a href="'+url+'">');
			att.html += '</a><div><img src="'+img_url+'"></div>';

		}
		this.ori_add2attachment_list(name, att, upload_id);
		if(att.complete){
			document.getElementById(name).style.height="auto";
		}
	};
}

