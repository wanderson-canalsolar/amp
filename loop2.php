<?php while(amp_loop('start')): ?>
   
<div class="loop-post2 loops2">
    <div id="id-teste-amp-img-loop2"><?php amp_loop_image(); ?></div>
    <?php amp_loop_category(); ?>
    <?php amp_loop_title(); ?>
    <?php amp_loop_excerpt(); ?>
    <?php amp_loop_date(); ?>
</div>
<?php endwhile; amp_loop('end');  ?>
<?php amp_pagination(); ?>