<h1>Concerts</h1>

<?php $__currentLoopData = $concerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $concert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <h2><?php echo e($concert->title); ?></h2>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>