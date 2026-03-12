<?php
$googleAdsId = 'AW-SEU_ID_AQUI'; 
?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $googleAdsId; ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', '<?php echo $googleAdsId; ?>');
</script>
