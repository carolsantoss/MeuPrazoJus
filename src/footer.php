<footer class="main-footer">
    <div class="max-w-7xl footer-content" style="display: flex; flex-wrap: wrap; gap: 2rem; justify-content: space-between; text-align: left; align-items: flex-start;">
        
        <div class="footer-column" style="flex: 1; min-width: 200px;">
            <h4 style="color: white; margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem;">MeuPrazoJus</h4>
            <ul style="list-style: none; padding: 0; margin: 0; line-height: 2;">
                <li><a href="index" style="color: var(--text-muted); text-decoration: none; transition: color 0.2s;">Calculadora de Prazos</a></li>
                <li><a href="blog" style="color: var(--text-muted); text-decoration: none; transition: color 0.2s;">Blog</a></li>
                <li><a href="sobre" style="color: var(--text-muted); text-decoration: none; transition: color 0.2s;">Sobre Nós</a></li>
            </ul>
        </div>

        <div class="footer-column" style="flex: 1; min-width: 200px;">
            <h4 style="color: white; margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem;">Legal</h4>
            <ul style="list-style: none; padding: 0; margin: 0; line-height: 2;">
                <li><a href="politica-de-privacidade" style="color: var(--text-muted); text-decoration: none; transition: color 0.2s;">Política de Privacidade</a></li>
                <li><a href="termos-de-uso" style="color: var(--text-muted); text-decoration: none; transition: color 0.2s;">Termos de Uso</a></li>
            </ul>
        </div>

        <div class="footer-column" style="flex: 1; min-width: 200px;">
            <h4 style="color: white; margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem;">Contato</h4>
            <ul style="list-style: none; padding: 0; margin: 0; line-height: 2;">
                <li><a href="mailto:fc.contato@outlook.com.br" style="color: var(--text-muted); text-decoration: none; transition: color 0.2s;">fc.contato@outlook.com.br</a></li>
                <li><a href="contato" style="color: var(--text-muted); text-decoration: none; transition: color 0.2s;">Página de Contato</a></li>
            </ul>
        </div>
    </div>
    
    <div style="max-width: 1200px; margin: 2rem auto 0; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,0.1); text-align: center; padding-left: 1rem; padding-right: 1rem;">
        <p class="footer-disclaimer" style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem; line-height: 1.5;">
            Este site fornece informações de caráter estimativo e educacional. Não constitui aconselhamento jurídico. Consulte sempre um advogado e os sistemas oficiais do Poder Judiciário.
        </p>
        <p style="font-size: 0.9rem; color: var(--text-muted);">
            &copy; <?php echo date('Y'); ?> MeuPrazoJus. Desenvolvido por <a href="https://fc.fmacedo.adv.br/" target="_blank" style="color: var(--primary); text-decoration: none;">FC Technology</a>.
        </p>
    </div>

    <a href="mailto:fc.contato@outlook.com.br?subject=Feedback MeuPrazoJus" class="feedback-corner" target="_blank">
        🐛 Bug / Sugestão
    </a>
</footer>

<div id="cookie-banner" style="display:none; position:fixed; bottom:0; left:0; right:0; background:rgba(30, 30, 46, 0.95); backdrop-filter:blur(10px); color:#fff; padding:16px 24px; z-index:9999; align-items:center; justify-content:space-between; border-top:2px solid var(--primary); box-shadow: 0 -4px 20px rgba(0,0,0,0.5); flex-wrap:wrap; gap:1rem;">
  <p style="margin:0; flex:1; min-width:250px; font-size:0.95rem; line-height:1.5;">
    🍪 Procuramos oferecer a melhor experiência. Este site utiliza cookies técnicos e de publicidade (Google) para melhorar sua experiência e exibir anúncios personalizados. Ao continuar navegando, você concorda com a nossa <a href="politica-de-privacidade" style="color:var(--primary); text-decoration:underline;">Política de Privacidade</a> e <a href="termos-de-uso" style="color:var(--primary); text-decoration:underline;">Termos de Uso</a>.
  </p>
  <div style="display:flex; gap:10px;">
      <button onclick="rejectCookies()" style="background:transparent; color:#ccc; border:1px solid #ccc; padding:8px 16px; border-radius:4px; font-weight:500; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='transparent'">Rejeitar</button>
      <button onclick="acceptCookies()" style="background:var(--primary); color:#111; padding:8px 16px; border:none; border-radius:4px; font-weight:600; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.filter='brightness(1.1)'" onmouseout="this.style.filter='none'">Aceitar e Fechar</button>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    if (!localStorage.getItem('cookie-consent')) {
        document.getElementById('cookie-banner').style.display = 'flex';
    }
});

function acceptCookies() {
    localStorage.setItem('cookie-consent', 'accepted');
    document.getElementById('cookie-banner').style.display = 'none';
}

function rejectCookies() {
    localStorage.setItem('cookie-consent', 'rejected');
    document.getElementById('cookie-banner').style.display = 'none';
}
</script>

<style>
.footer-column a:hover { color: var(--primary) !important; }
</style>
