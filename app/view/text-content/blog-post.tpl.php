<h1><?=$title;?></h1>

<article class="blog-post">
    <header>
      <h2><?=$post->title;?></h2>
      <strong>Published:</strong>
      <time datetime="<?=$post->published;?>"><?=$post->published;?></time>
<?php if(!is_null($post->updated) && $post->updated > $post->published) : ?>
      (<time datetime="<?=$post->updated;?>" title="Updated"><?=$post->updated;?></time>)
<?php endif; ?>
      <!--• <strong>Author:</strong> <a href="<!--?=$post->authorUrl;?>"><!--?=$post->authorName;?></a>-->
    </header>		
    <section class="ingress"><?=$post->ingress;?></section>
    <section class="bodytext">
      <?=$post->text;?>
    </section>
    <section class="tag-section">
      <ul class="tags">
  <?php foreach($post->tags as $tag) : ?>
        <li><a href="<?=$tag->url;?>" class="tag"><?=$tag->tag;?></a></li>
  <?php endforeach; ?>
      </ul>
    </section>
    <section class="permalink">
      <strong>Permalink:</strong><br />
      <?=$post->showUrl;?>
    </section>
</article>
<footer class="bloglinks">
  <a href="<?=$blogIndexUrl;?>">More blog posts</a> |
  <a href="<?=$post->editUrl;?>">Edit this blog post</a>
</footer>