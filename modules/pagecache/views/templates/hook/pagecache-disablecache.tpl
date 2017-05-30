{*
* Page Cache powered by Jpresta (jpresta . com)
* 
*    @author    Jpresta
*    @copyright Jpresta
*    @license   You are just allowed to modify this copy for your own use. You must not redistribute it. License
*               is permitted for one Prestashop instance only but you can install it on your test instances.
*}
<script type="text/javascript">
$(document).ready(function() {
	$("a").each(function() {
	    var e = $(this).attr("href");
	    var t = this.search;
	    var n = "_pcnocache=" + (new Date().getTime());
	    var r = baseDir.replace("https", "http");
	    if (typeof e != "undefined" && e.substr(0, 1) != "#" && e.replace("https", "http").substr(0, r.length) == r) {
	        if (t.length == 0) this.search = n;
	        else this.search += "&" + n
	    }
	})
});
</script>
