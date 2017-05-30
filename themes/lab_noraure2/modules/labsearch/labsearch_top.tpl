<!--  block search  -->

<div class=" clearfix" id="search_block_top">

    <div class="current active">

        <i class="icon-search"></i>

    </div>

    <ul class="toogle_content" >

        <li>

        <form id="searchbox" method="get" action="{$link->getPageLink('search')|escape:'html':'UTF-8'}" >

                <input type="hidden" value="search" name="controller">

                <input type="hidden" value="position" name="orderby">

                <input type="hidden" value="desc" name="orderway">

                <input type="text" value="" placeholder="Recherchez" name="search_query" id="search_query_top" class="search_query form-control ac_input" autocomplete="off">

                <button class="btn btn-default button-search" name="submit_search" type="submit">

                    <span>Recherchez</span>

                </button>

            </form>

        </li>

    </ul>

</div>

<div id="search_autocomplete" class="search-autocomplete"></div>



</div>

<!--  end search -->







{include file="$self/labsearch_instant.tpl"}

