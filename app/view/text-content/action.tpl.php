<h1><?=$title;?></h1>

<div class="confirm-action">
  <h3>Sure you want to <?=$toDo;?> <?=$toWhat?><?php if(isset($which)) echo " ({$which})";?>?</h3>
  
  <?=$form;?>
</div>