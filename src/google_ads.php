<?php
// CONFIGURAÇÃO DO GOOGLE ADS
// Substitua 'AW-SEU_ID_AQUI' pelo seu ID de conversão real (ex: AW-123456789)
$googleAdsId = 'AW-SEU_ID_AQUI'; 
?>
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $googleAdsId; ?>"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', '<?php echo $googleAdsId; ?>');
</script>
