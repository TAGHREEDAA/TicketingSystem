<h1><?php echo e($concert->title); ?></h1>
<p><?php echo e($concert->description); ?></p>



<h2><?php echo e($concert->FormattedDate); ?></h2>


<h2><?php echo e($concert->FormattedTime); ?></h2>

<h2><?php echo e($concert->DollarsPrice); ?></h2>


<h2><?php echo e($concert->venue); ?></h2>
<h3><?php echo e($concert->venue_address); ?></h3>
<h2><?php echo e($concert->city); ?>, <?php echo e($concert->state); ?> <?php echo e($concert->zip); ?></h2>
<p><?php echo e($concert->additional_info); ?></p>