<?php while(amp_loop('start')): ?>
    <?php $contador; ?>
    <?php $contador2; ?>

<div class="loop-post<?php echo ++$contador;?> loops">
    <div id="id-teste-amp-img<?php echo ++$contador2;?>"><?php amp_loop_image(); ?></div>
    <?php amp_loop_category(); ?>
    <?php amp_loop_title(); ?>
    <?php amp_loop_excerpt(); ?>
    <?php amp_loop_date(); ?>
</div>
<?php endwhile; amp_loop('end');  ?>
<?php amp_pagination(); ?>