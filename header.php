<?php amp_header_core() ?>
 <header class="header container">
        
        <div class="left">
            <?php amp_logo(); ?>
        </div>
        
        <div class="right">
            <?php amp_call_now(); ?>
            <?php amp_search();?>

            <!-- <?php amp_social([
                'twitter' => 'https://twitter.com/ampforwp',
                'facebook' => 'https://facebook.com/ampforwp'
            ]);?>     -->
            <?php amp_sidebar(['action'=>'open-button']); ?>         
        </div>
        
        <div class="clearfix"></div>
     
</header>

<!-- POTENCIAS -->
<div class="potencias-amp" style="background-color: #EEEEEE;display:block;">
    <div class="pot-gc" style="align-items: center;justify-content: center;float:left;margin-left: 5%;">
        <div class="" style="text-align: center;padding: 0;">
        </div>
        <div class="" style="text-align: center;color:#E64F37;">
            <span style="font-size: 12px;font-weight:bold;color:#E64F37">No Brasil Hoje</span>
        </div>
        <div class="" style="text-align: center;color:#E64F37;">
        </div>
        <div class="" style="
        text-align: center;
    "><span style="font-size: 14px;font-weight:bold;color:#E64F37">GC 3,43 GW</span>
        </div>
    </div>

    <div class="pot-gd" style="align-items: center;justify-content: center;float:right;margin-right: 10%;">
        <div class="" style="text-align: center;padding: 0;">
            
        </div>
        <div class="" style="text-align: center;color:#53658C;">
            <span style="font-size: 12px;font-weight:bold;color:#53658C">No Brasil Hoje</span>
        </div>
        <div class="" style="text-align: center;color:#53658C;">
        </div>
        <div class="" style="
        text-align: center;
    "><span style="font-size: 14px;font-weight:bold;color:#53658C">GD 6,32 GW</span>
        </div>
    </div>
</div>

<!-- FIM -->
<?php amp_sidebar(['action'=>'start',
    'id'=>'sidebar',
    'layout'=>'nodisplay',
    'side'=>'right'
] ); ?>
<?php amp_sidebar(['action'=>'close-button']); ?>
<?php amp_menu(); ?>
<!-- <?php amp_search();?> -->
<?php amp_social(); ?> 
<?php amp_sidebar(['action'=>'end']); ?>
<div class="content-wrapper container">
    