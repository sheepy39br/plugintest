<?php
/**
 * WooCommerce – Bulk Product Descriptions
 * Path: admin/views/woo.php
 * Generează în lot descrieri pentru produse lipsă / sau rescrie opțional.
 */
if ( ! defined('ABSPATH') ) exit;

$opts = get_option('ai_bgpro_options', [ 'theme'=>'dark' ]);
$theme = esc_attr($opts['theme'] ?? 'dark');
?>
<div id="ai-bgpro-wrap" class="ai-bgpro-app" data-theme="<?php echo $theme; ?>">
  <div class="ai-bgpro-container">

    <!-- Tabs vizuale -->
    <div class="ai-bgpro-topbar">
      <div class="tabs">
        <a class="tab" href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro') ); ?>">Dashboard</a>
        <a class="tab" href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro-generate') ); ?>">Generate</a>
        <a class="tab" href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro-seo') ); ?>">SEO</a>
        <a class="tab active" href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro-woo') ); ?>">WooCommerce</a>
        <a class="tab" href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro-settings') ); ?>">Settings</a>
      </div>
      <div class="right"><span class="badge">Modern 2025</span></div>
    </div>

    <?php if ( ! class_exists('WooCommerce') ): ?>
      <div class="card">
        <h2>WooCommerce</h2>
        <p class="hint">WooCommerce nu este activ. Instalează/activează pluginul WooCommerce pentru a folosi această funcție.</p>
        <p><a class="button button-primary" href="<?php echo esc_url( admin_url('plugin-install.php?s=woocommerce&tab=search&type=term') ); ?>">Instalează WooCommerce</a></p>
      </div>
    <?php else: ?>

      <!-- Control Panel -->
      <div class="card">
        <h2>Bulk Product Descriptions</h2>
        <p class="hint">Generează automat descrieri pentru produsele fără descriere sau rescrie pe cele existente. Recomandat: runde de 20–50 produse pentru stabilitate.</p>

        <form method="post" action="<?php echo esc_url( admin_url('admin-post.php') ); ?>">
          <?php wp_nonce_field('ai_bgpro_run_bulk_woo'); ?>
          <input type="hidden" name="action" value="ai_bgpro_run_bulk_woo" />

          <div class="row">
            <div class="col" style="grid-column:span 3;">
              <label>Batch size</label>
              <select name="batch">
                <option value="10">10</option>
                <option value="20" selected>20</option>
                <option value="50">50</option>
                <option value="100">100</option>
              </select>
            </div>

            <div class="col" style="grid-column:span 5;">
              <label>Mode</label>
              <select name="mode">
                <option value="missing" selected>Doar produse fără descriere</option>
                <option value="overwrite">Rescrie toate descrierile</option>
              </select>
            </div>

            <div class="col" style="grid-column:span 4;">
              <label>Category (optional)</label>
              <?php
              wp_dropdown_categories([
                'taxonomy'         => 'product_cat',
                'name'             => 'product_cat',
                'show_option_all'  => '— All —',
                'hide_empty'       => 0,
                'orderby'          => 'name',
                'hierarchical'     => 1,
                'show_count'       => 0,
                'option_none_value'=> 0,
              ]);
              ?>
            </div>
          </div>

          <div class="row">
            <div class="col" style="grid-column:span 6;">
              <label>Tone</label>
              <select name="tone">
                <option value="Informative" selected>Informative</option>
                <option value="Professional">Professional</option>
                <option value="Casual">Casual</option>
                <option value="Persuasive">Persuasive</option>
              </select>
            </div>
            <div class="col" style="grid-column:span 6;">
              <label>Length</label>
              <select name="length">
                <option value="Short">Short (~80–120 cuvinte)</option>
                <option value="Medium" selected>Medium (~150–220)</option>
                <option value="Long">Long (~250–350)</option>
              </select>
            </div>
          </div>

          <p class="hint">AI-ul folosește titlul produsului + atribute (dacă există). După generare, poți edita manual din Products → Edit.</p>
          <p><button class="button button-primary">▶︎ Run Bulk</button></p>
        </form>
      </div>

      <!-- Quick Snapshot -->
      <div class="card">
        <h3>Snapshot stoc</h3>
        <?php
        $missing_count = 0; $total = 0;

        $q = new WP_Query([
          'post_type'      => 'product',
          'post_status'    => 'publish',
          'posts_per_page' => 10,
          'fields'         => 'ids'
        ]);

        if ( $q->have_posts() ) {
          echo '<table class="widefat striped"><thead><tr><th>Produs</th><th>Status</th></tr></thead><tbody>';
          foreach ( $q->posts as $pid ) {
            $total++;
            $has = trim( wp_strip_all_tags( get_post_field('post_content', $pid) ) ) !== '';
            if ( ! $has ) $missing_count++;
            echo '<tr>';
              echo '<td><a href="'.esc_url( get_edit_post_link($pid) ).'">'.esc_html( get_the_title($pid) ).'</a></td>';
              echo '<td>'. ( $has ? '✅ are descriere' : '❌ lipsă descriere' ) .'</td>';
            echo '</tr>';
          }
          echo '</tbody></table>';
        } else {
          echo '<p class="hint">Nu s-au găsit produse publicate.</p>';
        }
        ?>
        <p class="hint" style="margin-top:8px;">
          Total afișate: <strong><?php echo intval($total); ?></strong> &nbsp;•&nbsp;
          Fără descriere: <strong><?php echo intval($missing_count); ?></strong>
        </p>
      </div>

      <!-- Note -->
      <div class="card">
        <h3>Note & bune practici</h3>
        <ul style="margin:0 0 0 18px; list-style: disc;">
          <li>Rulează în loturi (20–50) pentru a evita timeouts.</li>
          <li>Dacă ai multe produse, rulează de mai multe ori butonul „Run Bulk”.</li>
          <li>Asigură-te că ai setat OpenAI API Key în <a href="<?php echo esc_url( admin_url('admin.php?page=ai-bgpro-settings') ); ?>">Settings</a>.</li>
          <li>Rescrierea tuturor descrierilor poate dura — folosește „overwrite” cu atenție.</li>
        </ul>
      </div>

    <?php endif; ?>

  </div>
</div>
