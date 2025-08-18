<?php
/**
 * SEO – Keyword Planner View
 * Path: admin/views/seo.php
 * Analiză rapidă keyword (volum, dificultate, opportunity) + related + Add to Auto-Schedule.
 */
if ( ! defined('ABSPATH') ) exit;

$opts = get_option('ai_bgpro_options', [
  'theme' => 'dark',
]);
?>
<div id="ai-bgpro-wrap" class="ai-bgpro-app" data-theme="<?php echo esc_attr($opts['theme'] ?? 'dark'); ?>">
  <div class="ai-bgpro-container">

    <!-- Tabs vizuale (navigarea reală e în meniul WP) -->
    <div class="ai-bgpro-topbar">
      <div class="tabs">
        <a class="tab" href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro') ); ?>">Dashboard</a>
        <a class="tab" href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro-generate') ); ?>">Generate</a>
        <a class="tab active" href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro-seo') ); ?>">SEO</a>
        <a class="tab" href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro-woo') ); ?>">WooCommerce</a>
        <a class="tab" href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro-settings') ); ?>">Settings</a>
      </div>
      <div class="right">
        <span class="badge">Modern 2025</span>
      </div>
    </div>

    <!-- Keyword Analyze -->
    <div class="card">
      <h2 style="margin-bottom:10px;">Keyword Planner</h2>

      <div class="row">
        <div class="col" style="grid-column: span 6;">
          <label>Keyword</label>
          <input id="ai-bgpro-kw" placeholder="ex: weight loss diet" />
        </div>

        <div class="col" style="grid-column: span 2;">
          <label>&nbsp;</label>
          <button id="ai-bgpro-analyze" class="button button-primary">Analyze</button>
        </div>

        <div class="col" style="grid-column: span 4;">
          <label>&nbsp;</label>
          <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" style="display:flex; gap:8px">
            <?php wp_nonce_field('ai_bgpro_queue_keyword'); ?>
            <input type="hidden" name="action" value="ai_bgpro_queue_keyword"/>
            <input type="hidden" id="ai-bgpro-kw-hidden" name="keyword" value=""/>
            <button class="button">Add to Auto-Schedule</button>
          </form>
        </div>
      </div>

      <p class="hint">Valul 1: estimări locale (heuristic). Valul 2: date reale via Ahrefs/SEMrush/Google Ads API.</p>

      <div id="ai-bgpro-kw-result" style="margin-top:14px; display:none">
        <div class="grid kpi">
          <div class="kpi card">
            <div class="kpi-label">Volume</div>
            <div class="kpi-value" id="kw-vol">—</div>
          </div>
          <div class="kpi card">
            <div class="kpi-label">Difficulty</div>
            <div class="kpi-value" id="kw-diff">—</div>
          </div>
          <div class="kpi card">
            <div class="kpi-label">Opportunity</div>
            <div class="kpi-value" id="kw-score">—</div>
          </div>
        </div>

        <div class="card" style="margin-top:12px;">
          <h3 style="margin-bottom:8px;">Related keywords</h3>
          <table class="widefat striped">
            <thead>
              <tr>
                <th>Keyword</th>
                <th style="width:120px;">Volume</th>
                <th style="width:110px;">Diff</th>
                <th style="width:110px;">Score</th>
                <th style="width:120px;">Queue</th>
              </tr>
            </thead>
            <tbody id="kw-related"></tbody>
          </table>
          <p class="hint" style="margin-top:8px;">Click „Queue” pe oricare keyword related pentru a-l programa în Auto-Schedule.</p>
        </div>

        <div style="margin-top:12px; display:flex; gap:8px;">
          <a class="button" href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro-generate') ); ?>">Generate 3 articles now</a>
          <button id="ai-bgpro-queue3" class="button">Queue 3 (top score)</button>
        </div>
      </div>
    </div>

    <!-- Queue Overview -->
    <div class="card" style="margin-top:16px;">
      <h3 style="margin-bottom:8px;">Queued keywords</h3>
      <?php $queue = get_option('ai_bgpro_queue', []); ?>
      <table class="widefat striped">
        <thead><tr><th>Keyword</th><th style="width:140px;">Added</th><th style="width:120px;">Status</th></tr></thead>
        <tbody>
          <?php if (empty($queue)): ?>
            <tr><td colspan="3" class="hint">Queue is empty.</td></tr>
          <?php else: foreach ($queue as $item): ?>
            <tr>
              <td><?php echo esc_html($item['keyword'] ?? ''); ?></td>
              <td><?php echo esc_html($item['added'] ?? ''); ?></td>
              <td><?php echo esc_html($item['status'] ?? 'queued'); ?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>

  </div>
</div>

<script>
/**
 * Keyword Planner (Valul 1) – Analyze & queue.
 * Necesită: AI_BGPRO.ajax și AI_BGPRO.nonce din wp_localize_script.
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

  // Analyze keyword (AJAX -> ai_bgpro_analyze_keyword)
  $('#ai-bgpro-analyze').on('click', function(e){
    e.preventDefault();
    const kw = ($('#ai-bgpro-kw').val()||'').trim();
    if(!kw) return toast('Enter a keyword first.', 'err');

    // set hidden for Add to Auto-Schedule
    $('#ai-bgpro-kw-hidden').val(kw);

    $.post(AI_BGPRO.ajax, {
      action: 'ai_bgpro_analyze_keyword',
      _ajax_nonce: AI_BGPRO.nonce,
      keyword: kw
    }, function(resp){
      if(!resp || !resp.success) return toast('Analyze failed.', 'err');

      const d = resp.data || {};
      $('#ai-bgpro-kw-result').show();
      $('#kw-vol').text((d.volume||0).toLocaleString());
      $('#kw-diff').text(d.difficulty||'—');
      $('#kw-score').text(d.score||'—');

      const $tb = $('#kw-related').empty();
      (d.related||[]).forEach(r=>{
        const $btn = $('<button/>',{class:'button', text:'Queue'}).on('click', function(e2){
          e2.preventDefault();
          queueKeyword(r.keyword);
        });
        $('<tr/>')
          .append($('<td/>').text(r.keyword))
          .append($('<td/>').text((r.volume||0).toLocaleString()))
          .append($('<td/>').text(r.difficulty||'')) 
          .append($('<td/>').text(r.score||''))
          .append($('<td/>').append($btn))
          .appendTo($tb);
      });

      toast('Keyword analyzed.', 'ok');
    }).fail(()=> toast('Network error.', 'err'));
  });

  // Helper: queue one keyword via admin-post (non-AJAX submit)
  function queueKeyword(kw){
    // facem submit rapid către admin-post cu nonce-ul formularului existent?
    // Simplu: creăm dinamic un form mic
    const $f = $('<form/>',{method:'post', action:AI_BGPRO.ajax.replace('admin-ajax.php','admin-post.php')})
      .append('<?php echo wp_nonce_field('ai_bgpro_queue_keyword','_wpnonce', true, false); ?>')
      .append($('<input/>',{type:'hidden', name:'action',  value:'ai_bgpro_queue_keyword'}))
      .append($('<input/>',{type:'hidden', name:'keyword', value:kw}));
    $('body').append($f);
    $f.trigger('submit');
  }

  // Queue top 3 by score (from current related list)
  $('#ai-bgpro-queue3').on('click', function(e){
    e.preventDefault();
    const rows = Array.from(document.querySelectorAll('#kw-related tr'));
    if(!rows.length) return toast('No related keywords to queue.', 'err');

    // parse table into objects
    const data = rows.map(tr=>{
      const tds = tr.querySelectorAll('td');
      return {
        keyword: tds[0]?.textContent.trim(),
        volume:  parseInt((tds[1]?.textContent||'0').replace(/[^0-9]/g,''),10) || 0,
        diff:    parseInt((tds[2]?.textContent||'0'),10) || 0,
        score:   parseInt((tds[3]?.textContent||'0'),10) || 0
      };
    }).sort((a,b)=> b.score - a.score);

    const top3 = data.slice(0,3);
    if(!top3.length) return toast('No related keywords.', 'err');

    // submit three forms quickly (will redirect after first – acceptable simple approach)
    top3.forEach((item,i)=>{
      setTimeout(()=> queueKeyword(item.keyword), i*150);
    });
  });

})(jQuery);
</script>
