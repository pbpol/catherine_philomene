<div class="nav-container hidden-xs">
    <div id="menubar-pc" class="lab_custommenu">
		<div class="menuCenter">
			{$megamenu}
		</div>
    </div>
</div>
<div class="navmenu-mobile visible-xs">
	<h3 class="categ-title">{l s='Menu' mod='labmegamenus'}</h3>
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
			meanScreenWidth: "767",
			meanMenuContainer: 'body .navmenu-mobile'
		});
	});
</script>