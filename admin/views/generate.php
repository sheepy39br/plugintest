<?php
/**
 * Generate View – AI Blog Generator PRO
 * Path: admin/views/generate.php
 * Form pentru generare articol + preview live (AJAX).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$opts = get_option('ai_bgpro_options', [
  'industries' => ['Fitness','Beauty','Technology','Travel','Finance'],
  'theme'      => 'dark',
]);

$industries = $opts['industries'];
// Acceptă CSV sau array în opțiuni
if ( ! is_array($industries) ) {
  $industries = array_filter(array_map('trim', explode(',', (string)$industries)));
}
if ( empty($industries) ) {
  $industries = ['General'];
}
?>

<div id="ai-bgpro-wrap" class="ai-bgpro-app" data-theme="<?php echo esc_attr($opts['theme'] ?? 'dark'); ?>">
  <div class="ai-bgpro-container">

    <!-- Tabs vizuale (navigarea reală e din meniu) -->
    <div class="ai-bgpro-topbar">
      <div class="tabs">
        <a class="tab" href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro') ); ?>">Dashboard</a>
        <a class="tab active" href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro-generate') ); ?>">Generate</a>
        <a class="tab" href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro-seo') ); ?>">SEO</a>
        <a class="tab" href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro-woo') ); ?>">WooCommerce</a>
        <a class="tab" href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro-settings') ); ?>">Settings</a>
      </div>
      <div class="right">
        <span class="badge">Modern 2025</span>
      </div>
    </div>

    <!-- Form Generate -->
    <div class="card" style="grid-column: span 12;">
      <h2 style="margin-bottom:10px;">Generate Articles</h2>
      <form id="ai-bgpro-generate-form" onsubmit="return false;">
        <div class="row">
          <div class="col">
            <label>Title (optional)</label>
            <input type="text" id="ai-bgpro-title" placeholder="Lasă gol pentru titlu generat automat" />
          </div>
          <div class="col">
            <label>Keywords (comma separated)</label>
            <input type="text" id="ai-bgpro-keywords" placeholder="ex: weight loss, metabolism, calorie deficit" />
          </div>
          <div class="col">
            <label>Industry</label>
            <select id="ai-bgpro-industry">
              <?php foreach ($industries as $ind): ?>
                <option value="<?php echo esc_attr($ind); ?>"><?php echo esc_html($ind); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="row">
          <div class="col">
            <label>Tone</label>
            <select id="ai-bgpro-tone">
              <option value="Informative">Informative</option>
              <option value="Professional">Professional</option>
              <option value="Casual">Casual</option>
              <option value="Persuasive">Persuasive</option>
            </select>
          </div>
          <div class="col">
            <label>Length</label>
            <select id="ai-bgpro-length">
              <option value="Medium">Medium (~800–1200 cuvinte)</option>
              <option value="Short">Short (~400–700)</option>
              <option value="Long">Long (~1500+)</option>
            </select>
          </div>
          <div class="col">
            <label>&nbsp;</label>
            <button class="button button-primary" id="ai-bgpro-generate-btn">✨ Generate Now</button>
          </div>
        </div>

        <p class="hint">Conținutul se generează via OpenAI; imaginea featured poate fi adăugată via Unsplash (configurezi cheile în <a href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro-settings') ); ?>">Settings</a>).</p>
      </form>
    </div>

    <!-- Preview -->
    <div class="card" style="grid-column: span 12;">
      <h3 style="margin-bottom:8px;">Preview</h3>
      <div id="ai-bgpro-preview" style="min-height:160px; border:1px dashed var(--border); padding:12px;">
        <p class="hint" style="margin:0;">Nu există preview încă. Completează câmpurile și apasă „Generate Now”.</p>
      </div>
      <p class="hint" style="margin-top:8px;">După validare, articolul poate fi salvat ca draft sau publicat din editorul WordPress.</p>
    </div>

  </div>
</div>

<script>
/**
 * JS inline pentru preview rapid (AJAX).
 * Se bazează pe AI_BGPRO.ajax și AI_BGPRO.nonce setate în wp_localize_script în plugin.
 */
(function($){
  function toast(msg, type){
    const $n = $('<div/>').css({
      position:'fixed', right:'18px', bottom:'18px', zIndex:99999,
      background: type==='err' ? '#c25555' : '#2b6cb0',
      color:'#fff', padding:'10px 14px', borderRadius:'10px', boxShadow:'0 12px 30px rgba(0,0,0,.25)'
    }).text(msg);
    $('body').append($n);
    setTimeout(()=> $n.fadeOut(250, ()=> $n.remove()), 1800);
  }

  $('#ai-bgpro-generate-btn').on('click', function(e){
    e.preventDefault();

    const title    = ($('#ai-bgpro-title').val()||'').trim();
    const keywords = ($('#ai-bgpro-keywords').val()||'').trim();
    const industry = ($('#ai-bgpro-industry').val()||'').trim();
    const tone     = ($('#ai-bgpro-tone').val()||'Informative').trim();
    const length   = ($('#ai-bgpro-length').val()||'Medium').trim();

    // Prompt simplu pentru demo (endpoint-ul de server va folosi valorile cum vrei tu)
    const prompt = (title || keywords || 'blog post') + ' | industry: ' + industry + ' | tone: ' + tone + ' | length: ' + length;

    $('#ai-bgpro-preview').html('<p>⏳ Generating preview...</p>');

    $.post(AI_BGPRO.ajax, {
      action: 'ai_bgpro_generate',
      _ajax_nonce: AI_BGPRO.nonce,
      prompt: prompt
    }, function(resp){
      if (resp && resp.success && resp.data) {
        const html = '<h3 style="margin-top:0;">'+ (resp.data.title || 'Generated Article') +'</h3>' + (resp.data.content || '');
        $('#ai-bgpro-preview').html(html);
        toast('Preview generated.', 'ok');
      } else {
        $('#ai-bgpro-preview').html('<p class="hint">Nu s-a putut genera preview. Verifică cheia OpenAI în Settings.</p>');
        toast('Generation failed.', 'err');
      }
    }).fail(function(){
      $('#ai-bgpro-preview').html('<p class="hint">Eroare de rețea. Încearcă din nou.</p>');
      toast('Network error.', 'err');
    });
  });
})(jQuery);
</script>
