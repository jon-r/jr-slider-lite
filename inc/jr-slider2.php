<?php $galID = (isset($galID)) ? $galID + 1 : 1; //to allow multiple sliders ?>

<?php
  $imgObj = $opts['gallery'][0];

  $singleSlide = count($opts['gallery']) == 1;

//  $responsive = [
//    'lg' => get_option('large_size_w'),
//    'md' => get_option('medium_size_w')
//  ];

//  $srcset = sprintf(
//    '%s %dw, %s %dw, %s %dw',
//    $imgObj['sizes']['medium'], $responsive['md'],
//    $imgObj['sizes']['large'], $responsive['lg'],
//    $imgObj['url'], $imgObj['width']
//  );

$imgIDs = jr_get_image_ids($opts['gallery']);
?>

<section class="site-color" data-jr-gallery
   data-gallery-autoplay="<?php echo ($opts['autoplay']) ? $opts['autoplay'] : 'false'; ?>"
<?php /*  data-gallery-rwd-lg="<?php echo $responsive['lg'] ?>"
   data-gallery-rwd-md="<?php echo $responsive['md'] ?>"  */ ?>
   data-gallery-anim="<?php echo $opts['animation-duration'] ?>" >

  <div class="gallery-main" >

    <div class="gallery-focus gallery-list gallery-anim-<?php echo $opts['animation-type'] ?>"
         style="max-height:<?php echo $opts['max-height'] ?>;
            height:<?php echo $opts['gallery'][0]['height'] ?>px;
            transition-duration: <?php echo $opts['animation-duration'] ?>ms" >

      <div class="gallery-image active" data-image-id="<?php echo $imgIDs[0] ?>" >
<?php /*       <img src="<?php echo $opts['gallery'][0]['url'] ?>" srcset="<?php echo $srcset ?>" >--> */ ?>
        <?php echo wp_get_attachment_image( $imgIDs[0], 'full'); ?>
        <?php echo wp_get_attachment_label( $imgIDs[0] ) ?>
      </div>

      <?php for ($i = 1; $i < $n; $i++) : ?>
      <div class="gallery-image" data-image-id="<?php echo $imgIDs[$i] ?>" ></div>
      <?php endfor ?>
    </div>

  <?php /*  <div class="gallery-links" aria-hidden="true" style="display:none;visibility:hidden" >
      <?php for ($i = 0; $i < $n; $i++) : ?>
      <?php
        $imgObj = $opts['gallery'][$i];
        $sizeMd = $imgObj['sizes']['medium'];
        $sizeLg = $imgObj['sizes']['large'];
        $srcset = sprintf(
          '%s %dw, %s %dw, %s %dw',
          $sizeMd, $responsive['md'],
          $sizeLg, $responsive['lg'],
          $imgObj['url'], $imgObj['width']
        );
      ?>
      <a href="<?php echo $opts['gallery'][$i]['url'] ?>"
         data-gallery-img-id="<?php echo $imgIDs[$i] ?>"
         data-gallery-srcset="<?php echo $srcset ?>"
         data-gallery-md="<?php echo $sizeMd ?>"
         data-gallery-lg="<?php echo $sizeLg ?>" >
      </a>
      <?php endfor; ?>


    </div> */ ?>

    <?php if ($opts['has_arrow'] && !$singleSlide) : ?>
    <button class="gallery-controls gallery-arrow prev gallery-icon" data-go="prev"></button>
    <button class="gallery-controls gallery-arrow next gallery-icon" data-go="next"></button>
    <?php endif ?>

    <?php if ($opts['has_dots'] && !$singleSlide) : ?>
    <ul class="gallery-list gallery-controls gallery-blips" >
      <?php for ($i = 0; $i < $n; $i++) : ?>
      <li class=" <?php echo ($i == 0) ? 'active' : '' ?>" data-go="<?php echo $i ?>" ></li>
      <?php endfor; ?>
    </ul>
    <?php endif; ?>

  </div>

  <?php if ($opts['has_thumbs'] && !$singleSlide) : ?>
  <div class="gallery-list gallery-thumbs" >
    <?php for ($i = 0; $i < $n; $i++) : ?>
    <figure class="photo-select <?php echo ($i == 0) ? 'active' : '' ?>" data-go="<?php echo $i ?>" >
      <img src="<?php echo $opts['gallery'][$i]['sizes']['medium'] ?>"  >
    </figure>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</section>
