<h1><?=$title;?></h1>

<?php if($tag): ?>
<p>All blog posts tagged with: <span class="tag"><?=$tag;?></span></p>
<?php endif; ?>

<?php foreach($posts as $post): ?>
<article class="blog-post">
		<header>
      <h2><a href="<?=$post->showUrl;?>"><?=$post->title;?></a></h2>
      <strong>Published:</strong>
      <time datetime="<?=$post->published;?>"><?=$post->published;?></time>
  <?php if(isset($post->updated)) : ?>
      (<time datetime="<?=$post->updated;?>" title="Updated"><?=$post->updated;?></time>)
  <?php endif; ?>
      <!--<strong>Author:</strong> <a href="<?//=$post->authorUrl;?>"><?//=$post->authorName;?></a>-->
    </header>		
		<section class="ingress"><?=$post->ingress;?></section>
    <p><a href="<?=$post->showUrl;?>">Read more..</a></p>
    <ul class="tags">
  <?php if(isset($post->tags)): ?>
    <?php foreach($post->tags as $tag): ?>
      <li><a href="<?=$tag->url;?>" class="tag"><?=$tag->tag;?></a></li>
    <?php endforeach; ?>
  <?php endif; ?>
		</ul>
</article>
<?php endforeach; ?>
    