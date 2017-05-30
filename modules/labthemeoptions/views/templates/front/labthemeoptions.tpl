{if $lab_show_color!=0}

<style type="text/css">

    /*  header */
    {if $lab_hbgnav !=''}
        #header{
            background-color:{$lab_hbgnav};
        }
    {/if}
    {if $lab_htextnav !=''}

    {/if}
    {if $lab_hbgheadercolor !=''}

    {/if}
    {if $lab_htextheader !=''}

    {/if}
    {if $lab_hhovertext !=''}

    {/if}
    {if $lab_hhovertextnav !=''}

    {/if}
    /* end header */

   /*  main content */
    {if $lab_cbgcolor !=''}
    .columns-container {
         background-color:{$lab_cbgcolor};
    }
    {/if}
    {if $lab_ccolorlink !=''}

    {/if}
    {if $lab_chovercolorlink !=''}

    {/if}
    {if $lab_ccolortext !=''}

    {/if}
    {if $lab_ccolorprice !=''}

    {/if}
    {if $lab_ccoloroldprice !=''}

    {/if}
    {if $lab_ciconcolor !=''}

    {/if}
    {if $lab_cbgbuttom !=''}

    {/if}
    {if $lab_ctextbuttom !=''}

    {/if}
    {if $lab_ctextbuttomhover !=''}

    {/if}
    {if $lab_cbgbuttomhover !=''}

    {/if}

    /*  end main content */

    /*    megamenu  */

    {if $lab_mbghover !=''}

    {/if}
    {if $lab_mbgcolor !=''}

    {/if}
    {if $lab_mtext !=''}

    {/if}
    {if $lab_mtexthover !=''}

    {/if}
    {if $lab_mbgsubmenu !=''}

    {/if}
    {if $lab_mtextsub !=''}

    {/if}
    {if $lab_mhovertextsub !=''}

    {/if}


    /*   end magamenu */

    /*   footer */
    {if $lab_fbgcolor !=''}

    {/if}
    {if $lab_fcolortext !=''}

    {/if}
    {if $lab_flinkcolor !=''}

    {/if}
    {if $lab_flinkcolorhover !=''}

    {/if}

    /*  end  footer */

</style>

{/if}
{if $labshowthemecolor == 1 }
		
			{if $labthemecolor}
				<link rel="stylesheet" type="text/css" href="{$PS_BASE_URL}{$PS_BASE_URI}themes/{$LAB_THEMENAME}/css/color/global_{$labthemecolor}.css" />;
			{/if}
        
		
        {if $labskin }
			<style type="text/css">
				body{
					background-image: url("{$PS_BASE_URL}{$PS_BASE_URI}modules/labthemeoptions/views/templates/front/colortool/images/pattern/{$labskin}.png") ;
				}
			</style>
        {/if}
{/if}