<h1><?=$title;?></h1>

<? if($tag): ?>
<p>All blog posts tagged with: <span class="tag"><?=$tag;?></span></p>
<? endif; ?>

<? foreach($posts as $post): ?>
<article class="blog-post">
		<header>
      <h2><a href="<?=$post->showUrl;?>"><?=$post->title;?></a></h2>
      <strong>Published:</strong>
      <time datetime="<?=$post->published;?>"><?=$post->published;?></time>
  <? if(isset($post->updated)) : ?>
      (<time datetime="<?=$post->updated;?>" title="Updated"><?=$post->updated;?></time>)
  <? endif; ?>
      <!--<strong>Author:</strong> <a href="<?//=$post->authorUrl;?>"><?//=$post->authorName;?></a>-->
    </header>		
		<section class="ingress"><?=$post->ingress;?></section>
    <p><a href="<?=$post->showUrl;?>">Read more..</a></p>
    <ul class="tags">
  <? if(isset($post->tags)): ?>
    <? foreach($post->tags as $tag): ?>
      <li><a href="<?=$tag->url;?>" class="tag"><?=$tag->tag;?></a></li>
    <? endforeach; ?>
  <? endif; ?>
		</ul>
</article>
<? endforeach; ?>
    