<h1><?=$title;?></h1>

<p>A list on every text content in database.</p>

<div class="content-list-filter">
  <?=$form;?>
</div>

<table class="content-list">
  <tr>
    <th>Type</th>
    <th>Title</th>
    <th>Published</th>
    <th>Actions</th>
  </tr>
<? foreach($contents AS $content) : ?>
  <tr>
    <td><?=$content->typeTxt;?></td>
    <td>
      <a href="<?=$content->showUrl;?>"><?=$content->title;?></a>
    </td>
    <td>
      <time datetime="<?=$content->published;?>" title="<?=$content->published;?>" class="<?=$content->available;?>">
        <?=$content->publishedTxt;?>
      </time>
    </td>
    <td>
      <a href="<?=$content->showUrl;?>" title="View content">View</a> |
      <a href="<?=$content->editUrl;?>" title="Edit content">Edit</a> |
      <a href="<?=$content->removeUrl;?>" title="Delete content">Delete</a>
    </td>
	</tr>
<? endforeach; ?>
</table>
<p></p>
<p>
  <a href="<?=$addUrl;?>">New content</a> | 
  <a href="<?=$setupUrl;?>">Restore content database tables</a>
</p>