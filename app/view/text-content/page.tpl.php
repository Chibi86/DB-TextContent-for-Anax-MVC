<h1><?=$title;?></h1>

<article class="page-content">
  <section class="ingress">
    <?=$page->ingress;?>
  </section>
  <section class="text">
   <?=$page->text;?>
  </section>
</article>
<footer class="page-footer">
  <strong>Published:</strong> <time datetime="<?=$page->published;?>"><?=$page->published;?></time>
  <!--| by <a href="<--?=$page->authorUrl;?>"><--?=$page->authorName;?></a>-->
<?php if(!is_null($page->updated) && $page->updated > $page->published) : ?>
  | Last updated: <time datetime="<?=$page->updated;?>"><?=$page->updated;?></time>
<?php endif; ?>
  <a href="<?=$page->editUrl;?>">Edit page</a>
</footer>