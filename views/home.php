<?php $this->inc('app') ?>

<?php $this->startSection('app-content') ?>

<h1>Home</h1>

<p><a href="/main/page/1">Link 1</a></p>
<p><a href="/main/page/2">Link 2</a></p>
<p><a href="/main/page/3">Link 3</a></p>

<div>
	{{@content}}
</div>

<?php $this->endSection() ?>