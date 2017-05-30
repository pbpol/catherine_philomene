<div class="nav-container visible-lg visible-md">
    <div id="menubar-pc" class="lab_custommenu">
		<div class="menuCenter">
			{$megamenu}
		</div>
    </div>
</div>
<div class="navmenu-mobile hidden-md hidden-lg">
<!-- 	<h3 class="categ-title">{l s='Menu' mod='labmegamenus'}</h3> -->
	<div class="mobile-menu">
		<nav>
			<ul>
				{$megamenumobile}
			</ul>
		</nav>
	</div>
</div>
<script type="text/javascript">
//<![CDATA[
var CUSTOMMENU_POPUP_EFFECT = {$effect};
var CUSTOMMENU_POPUP_TOP_OFFSET = {$top_offset};
//]]>
</script>
<script>
	jQuery(document).ready(function () {
	    jQuery('.mobile-menu nav').meanmenu({
			meanScreenWidth: "1070",
			meanMenuContainer: 'body .navmenu-mobile'
		});
	});
</script>