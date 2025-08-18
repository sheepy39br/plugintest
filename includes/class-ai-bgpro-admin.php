<?php
if ( ! defined('ABSPATH') ) exit;

class AI_BGPRO_Admin {

  
public static function topbar($active='dashboard'){
    $tabs = [
      'dashboard' => __('Dashboard','ai-blog-generator-pro'),
      'generate'  => __('Generate','ai-blog-generator-pro'),
      'seo'       => __('SEO','ai-blog-generator-pro'),
      'woo'       => __('WooCommerce','ai-blog-generator-pro'),
      'settings'  => __('Settings','ai-blog-generator-pro'),
    ];
    $meta = [
      'dashboard'=>['icon'=>'dashicons-dashboard','desc'=>'Overview & stats'],
      'generate' =>['icon'=>'dashicons-edit','desc'=>'Create articles fast'],
      'seo'      =>['icon'=>'dashicons-chart-area','desc'=>'Titles, meta & schema'],
      'woo'      =>['icon'=>'dashicons-cart','desc'=>'Bulk product content'],
      'settings' =>['icon'=>'dashicons-admin-generic','desc'=>'Keys & automation'],
    ];
    echo '<div class="ai-bgpro-topbar"><div class="tabs">';
    foreach ($tabs as $key=>$label) {
      $url = admin_url('admin.php?page='.AI_BGPRO_SLUG.'&tab='.$key);
      $cls = $key===$active ? 'tab active' : 'tab';
      $m = $meta[$key];
      echo '<a class="'.esc_attr($cls).'" href="'.esc_url($url).'">';
      echo '<span class="icon dashicons '.esc_attr($m['icon']).'"></span>';
      echo '<span class="label">'.esc_html($label).'</span>';
      echo '<span class="desc">'.esc_html($m['desc']).'</span>';
      echo '</a>';
    }
    echo '</div><div class="mode"></div></div>';
  }

  public static function view_dashboard
($opts){
    $queue = get_option('ai_bgpro_queue', []);
    echo '<div class="card"><h2>Welcome</h2><p>AI Blog Generator PRO – status rapid.</p></div>';
    echo '<div class="grid">';
    echo '<div class="card"><h3>Articles today</h3><p>'.intval(self::count_posts_today()).'</p></div>';
    echo '<div class="card"><h3>Queued keywords</h3><p>'.count($queue).'</p></div>';
    echo '<div class="card"><h3>Industries</h3><p>'.esc_html(implode(', ', $opts['industries'] ?? [])).'</p></div>';
    echo '</div>';
    echo '<div class="card"><h3>Quick Links</h3><p><a class="button button-primary" href="'.admin_url('admin.php?page='.AI_BGPRO_SLUG.'&tab=generate').'">Generate</a> ';
    echo '<a class="button" href="'.admin_url('admin.php?page='.AI_BGPRO_SLUG.'&tab=seo').'">SEO</a> ';
    echo '<a class="button" href="'.admin_url('admin.php?page='.AI_BGPRO_SLUG.'&tab=woo').'">WooCommerce</a></p></div>';
  }

  public static function view_generate($opts){
    $industries = $opts['industries'] ?? [];
    ?>
    <div class="card">
      <h2>Generate Articles</h2>
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('ai_bgpro_generate_now'); ?>
        <input type="hidden" name="action" value="ai_bgpro_generate_now"/>
        <div class="row">
          <div class="col">
            <label>Title (optional)</label>
            <input type="text" name="title" placeholder="Leave empty to auto-generate from keywords"/>
          </div>
          <div class="col">
            <label>Keywords (comma separated)</label>
            <input type="text" name="keywords" placeholder="weight loss, metabolism"/>
          </div>
        </div>
        <div class="row">
          <div class="col">
            <label>Tone</label>
            <select name="tone">
              <option>Informative</option><option>Professional</option><option>Casual</option><option>Persuasive</option>
            </select>
          </div>
          <div class="col">
            <label>Length</label>
            <select name="length">
              <option>Medium</option><option>Short</option><option>Long</option>
            </select>
          </div>
          <div class="col">
            <label>Industry</label>
            <select name="industry">
              <?php foreach ($industries as $ind): ?>
                <option><?php echo esc_html($ind); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <p><button class="button button-primary">Generate Now</button></p>
        <p class="hint">The plugin will call OpenAI for content and Unsplash for a featured image (if API keys are set in Settings).</p>
      
</form>
      <div class="card" style="margin-top:16px">
        <h3>Latest Drafts</h3>
        <div class="drafts">
          <?php
          $q = new WP_Query([
            'post_type'=>'post','post_status'=>'draft','posts_per_page'=>10,'orderby'=>'date','order'=>'DESC'
          ]);
          if ($q->have_posts()): echo '<ul class="draft-list">';
            while($q->have_posts()): $q->the_post();
              echo '<li><strong>'.esc_html(get_the_title()).'</strong> — '.esc_html(wp_trim_words(strip_tags(get_the_excerpt() ?: get_the_content()), 18)).' ';
              echo '<a class="button" href="'.esc_url(admin_url('post.php?action=edit&post='.get_the_ID())).'">Edit</a> ';
              echo '<a class="button" target="_blank" href="'.esc_url(get_preview_post_link()).'">Preview</a></li>';
            endwhile; echo '</ul>'; wp_reset_postdata();
          else: echo '<p>No drafts yet. Generate an article to see it here.</p>'; endif; ?>
        </div>
      </div>
    </div>

    <?php
  }

  public static function view_seo($opts){
    ?>
    <div class="card">
      <h2>Keyword Planner</h2>
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="row">
        <?php wp_nonce_field('ai_bgpro_queue_keyword'); ?>
        <input type="hidden" name="action" value="ai_bgpro_queue_keyword"/>
        <div class="col">
          <label>Keyword</label>
          <input name="keyword" placeholder="weight loss diet"/>
        </div>
        <div class="col">
          <label>&nbsp;</label>
          <button class="button">Add to Auto-Schedule</button>
        </div>
      </form>
      <p class="hint">Valul 1: volum/dificultate estimate; Valul 2: integrare Ahrefs/SEMrush/Google Ads API.</p>
    </div>
    <?php
  }

  public static function view_woo($opts){
    if ( ! class_exists('WooCommerce') ) {
      echo '<div class="card"><h2>WooCommerce</h2><p>WooCommerce not detected. Please install/activate WooCommerce.</p></div>';
      return;
    }
    ?>
    <div class="card">
      <h2>Bulk Product Descriptions</h2>
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('ai_bgpro_run_bulk_woo'); ?>
        <input type="hidden" name="action" value="ai_bgpro_run_bulk_woo"/>
        <p>Process products without description (max 20 per run).</p>
        <button class="button button-primary">Run Bulk</button>
      
</form>
      <div class="card" style="margin-top:16px">
        <h3>Latest Drafts</h3>
        <div class="drafts">
          <?php
          $q = new WP_Query([
            'post_type'=>'post','post_status'=>'draft','posts_per_page'=>10,'orderby'=>'date','order'=>'DESC'
          ]);
          if ($q->have_posts()): echo '<ul class="draft-list">';
            while($q->have_posts()): $q->the_post();
              echo '<li><strong>'.esc_html(get_the_title()).'</strong> — '.esc_html(wp_trim_words(strip_tags(get_the_excerpt() ?: get_the_content()), 18)).' ';
              echo '<a class="button" href="'.esc_url(admin_url('post.php?action=edit&post='.get_the_ID())).'">Edit</a> ';
              echo '<a class="button" target="_blank" href="'.esc_url(get_preview_post_link()).'">Preview</a></li>';
            endwhile; echo '</ul>'; wp_reset_postdata();
          else: echo '<p>No drafts yet. Generate an article to see it here.</p>'; endif; ?>
        </div>
      </div>
    </div>

    <?php
  }

  public static function view_settings($opts){
    ?>
    <div class="card">
      <h2>API Providers & Keys</h2>
      <p class="hint">Select provider and add its key. OpenAI, Groq și DeepSeek folosesc endpoint compatibil; Gemini are endpoint propriu.</p>
      <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <?php wp_nonce_field('ai_bgpro_save_settings'); ?>
        <input type="hidden" name="action" value="ai_bgpro_save_settings"/>
        <div class="row">
          <div class="col">
            <label>Provider</label>
            <?php $provider = $opts['provider'] ?? 'openai'; ?>
            <select name="provider">
              <option value="openai" <?php selected($provider,'openai'); ?>>OpenAI</option>
              <option value="groq" <?php selected($provider,'groq'); ?>>Groq</option>
              <option value="gemini" <?php selected($provider,'gemini'); ?>>Gemini</option>
              <option value="deepseek" <?php selected($provider,'deepseek'); ?>>DeepSeek</option>
            </select>
          </div>
          <div class="col">
            <label>OpenAI API Key</label>
            <input name="openai_key" value="<?php echo esc_attr($opts['openai_key'] ?? ''); ?>" placeholder="sk-..."/>
            <p style="margin-top:8px"><button type="button" class="button" id="ai_bgpro_test_key">Test OpenAI</button> <span id="ai_bgpro_test_result" class="ai-bgpro-chip" style="margin-left:8px;"></span></p>
          </div>
          <div class="col">
            <label>Unsplash API Key</label>
            <input name="unsplash_key" value="<?php echo esc_attr($opts['unsplash_key'] ?? ''); ?>" placeholder="your_key_here"/>
          </div>
        </div>

</div>
<div class="row">
  <div class="col">
    <label>Groq API Key</label>
    <input name="groq_key" value="<?php echo esc_attr($opts['groq_key'] ?? ''); ?>" placeholder="gsk_xxx"/>
  </div>
  <div class="col">
    <label>Gemini API Key</label>
    <input name="gemini_key" value="<?php echo esc_attr($opts['gemini_key'] ?? ''); ?>" placeholder="AIza..."/>
  </div>
  <div class="col">
    <label>DeepSeek API Key</label>
    <input name="deepseek_key" value="<?php echo esc_attr($opts['deepseek_key'] ?? ''); ?>" placeholder="sk-..."/>
  </div>
</div>
<div class="row">
  <div class="col">
    <label>OpenAI Model</label>
    <input name="model_openai" value="<?php echo esc_attr($opts['model_openai'] ?? 'gpt-4o-mini'); ?>"/>
  </div>
  <div class="col">
    <label>Groq Model</label>
    <input name="model_groq" value="<?php echo esc_attr($opts['model_groq'] ?? 'llama-3.1-70b-versatile'); ?>"/>
  </div>
  <div class="col">
    <label>Gemini Model</label>
    <input name="model_gemini" value="<?php echo esc_attr($opts['model_gemini'] ?? 'gemini-1.5-flash'); ?>"/>
  </div>
  <div class="col">
    <label>DeepSeek Model</label>
    <input name="model_deepseek" value="<?php echo esc_attr($opts['model_deepseek'] ?? 'deepseek-chat'); ?>"/>
  </div>
</div>

        <h3>Automation & Scheduling</h3>
        <div class="row">
          <div class="col">
            <label>Articles per day</label>
            <input name="articles_per_day" type="number" min="1" max="24" value="<?php echo intval($opts['articles_per_day'] ?? 3); ?>"/>
          </div>
          <div class="col">
            <label>Run times (CSV)</label>
            <input name="run_times" value="<?php echo esc_attr($opts['run_times'] ?? '09:00,14:00,20:00'); ?>" placeholder="09:00,14:00,20:00"/>
          </div>
          <div class="col">
            <label>Auto publish</label>
            <select name="auto_publish">
              <option value="enabled" <?php selected(($opts['auto_publish'] ?? 'enabled'),'enabled'); ?>>Enabled</option>
              <option value="disabled" <?php selected(($opts['auto_publish'] ?? 'enabled'),'disabled'); ?>>Disabled</option>
            </select>
          </div>
        </div>

        <h3>Industries</h3>
        <div class="row">
          <div class="col" style="grid-column: span 3;">
            <label>Select industries</label>
            <?php $all = ['Fitness','Beauty','Technology','Travel','Finance','Health','Real Estate','Food','Education','Events','Automotive','Fashion']; 
                  $selected = $opts['industries'] ?? []; ?>
            <select name="industries[]" multiple size="8" style="width:100%">
              <?php foreach ($all as $opt): ?>
                <option value="<?php echo esc_attr($opt); ?>" <?php echo in_array($opt, $selected, true) ? 'selected' : ''; ?>><?php echo esc_html($opt); ?></option>
              <?php endforeach; ?>
              <?php 
if ($selected) {
  foreach ($selected as $sel) {
    if (!in_array($sel,$all,true)) {
      echo '<option value="'.esc_attr($sel).'" selected>'.esc_html($sel).'</option>';
    }
  }
}
?>
            </select>
            <p class="hint">Ctrl/Cmd pentru selecții multiple. Elemente din lista salvată rămân bifate.</p>
            <input type="hidden" name="industries_raw" value="<?php echo esc_attr(implode(',',$selected)); ?>"/>
          </div>
        </div>

        <h3>Industries</h3>
        <p class="hint">Comma separated (you can add/remove as you wish)</p>
        <input name="industries" value="<?php echo esc_attr(implode(', ', $opts['industries'] ?? [])); ?>"/>

        <h3>Theme</h3>
        <select name="theme">
          <option value="dark"  <?php selected(($opts['theme'] ?? 'dark')==='dark'); ?>>Dark</option>
          <option value="light" <?php selected(($opts['theme'] ?? 'dark')==='light'); ?>>Light</option>
        </select>

        <p><button class="button button-primary">Save Settings</button></p>
      
</form>
      <div class="card" style="margin-top:16px">
        <h3>Latest Drafts</h3>
        <div class="drafts">
          <?php
          $q = new WP_Query([
            'post_type'=>'post','post_status'=>'draft','posts_per_page'=>10,'orderby'=>'date','order'=>'DESC'
          ]);
          if ($q->have_posts()): echo '<ul class="draft-list">';
            while($q->have_posts()): $q->the_post();
              echo '<li><strong>'.esc_html(get_the_title()).'</strong> — '.esc_html(wp_trim_words(strip_tags(get_the_excerpt() ?: get_the_content()), 18)).' ';
              echo '<a class="button" href="'.esc_url(admin_url('post.php?action=edit&post='.get_the_ID())).'">Edit</a> ';
              echo '<a class="button" target="_blank" href="'.esc_url(get_preview_post_link()).'">Preview</a></li>';
            endwhile; echo '</ul>'; wp_reset_postdata();
          else: echo '<p>No drafts yet. Generate an article to see it here.</p>'; endif; ?>
        </div>
      </div>
    </div>

    <?php
  }

  private static function count_posts_today(){
    $args = [
      'post_type' => 'post',
      'date_query' => [ ['after' => 'today'] ],
      'posts_per_page' => -1,
      'fields' => 'ids'
    ];
    $q = new WP_Query($args);
    return $q->found_posts;
  }
}
