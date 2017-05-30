{if isset($posts) AND !empty($posts)}
<div id="recent_article_smart_blog_block_left"  class="block blogModule boxPlain">
   <h2 class='sdstitle_block'><a href="{smartblog::GetSmartBlogLink('smartblog')}">{l s='Late Posts' mod='smartblogrecentposts'}</a></h2>
   <div class="block_content sdsbox-content">
      <ul class="recentArticles">
        {foreach from=$posts item="post"}
             {assign var="options" value=null}
             {$options.id_post= $post.id_smart_blog_post}
             {$options.slug= $post.link_rewrite}
             <li>
                  <div class="box-left">
                     <a class="image" title="{$post.meta_title}" href="{smartblog::GetSmartBlogLink('smartblog_post',$options)}">
                         <img alt="{$post.meta_title}" src="{$post.thumb_image}">
                     </a>
                  </div>
                <div class="box-right">
                   <div class="content"><a class="title"  title="{$post.meta_title}" href="{smartblog::GetSmartBlogLink('smartblog_post',$options)}">{$post.meta_title}</a></div>
                    <div class="info">{$post.created|date_format:"%b %d, %Y"}</div>
               </div>
             </li>
         {/foreach}
            </ul>
   </div>
</div>
{/if}