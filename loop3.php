<?php while(amp_loop('start')): ?>
   
<div class="loop-post3 loops3">
    <div id="id-teste-amp-img-loop3"><?php amp_loop_image(); ?></div>
    <?php amp_loop_category(); ?>
    <?php amp_loop_title(); ?>
    <?php amp_loop_excerpt(); ?>
    <?php amp_loop_date(); ?>
</div>
<?php endwhile; amp_loop('end');  ?>
<?php amp_pagination(); ?>