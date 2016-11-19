<h1><?=$title;?></h1>

<article class="blog-post">
		<header>
      <h2><?=$post->title;?></h2>
      <strong>Published:</strong>
      <time datetime="<?=$post->published;?>"><?=$post->published;?></time>
<? if(isset($post->updated)) : ?>
      (<time datetime="<?=$post->updated;?>" title="Updated"><?=$post->updated;?></time>)
<? endif; ?>
      <!--• <strong>Author:</strong> <a href="<?//=$post->authorUrl;?>"><?//=$post->authorName;?></a>-->
    </header>		
		<section class="ingress"><?=$post->ingress;?></section>
    <section class="bodytext">
			<?=$post->text;?>
		</section>
    <section class="tag-section">
      <ul class="tags">
  <? foreach($post->tags as $tag) : ?>
        <li><a href="<?=$tag->url;?>" class="tag"><?=$tag->tag;?></a></li>
  <? endforeach; ?>
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