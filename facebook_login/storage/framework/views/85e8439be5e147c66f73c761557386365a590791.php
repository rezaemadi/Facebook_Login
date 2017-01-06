<?php $__env->startSection('content'); ?>
<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Dashboard</div>
                <div class="panel-body">
                    You are logged in: social sweethearts® GmbH Breite!
                    <br>
                    <img src="<?php echo e(Auth::user()->avatar); ?>" style="width:80px; height:80px; border-radius:50%;"><br>
                    <b>user name is :</b> <?php echo e(Auth::user()->name); ?><br>
                    <b>user facebook id is :</b> <?php echo e(Auth::user()->facebook_id); ?><br>
                    <b>user email is :</b> <?php echo e(Auth::user()->email); ?><br>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_except(get_defined_vars(), array('__data', '__path')))->render(); ?>