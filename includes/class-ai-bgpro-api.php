<?php
if ( ! defined('ABSPATH') ) exit;

class AI_BGPRO_API {

private static function provider_request($prompt, $opts){
  $provider = $opts['provider'] ?? 'openai';
  if ($provider === 'groq' || $provider === 'deepseek' || $provider === 'openai') {
    // OpenAI-compatible
    $key = $provider==='groq' ? ($opts['groq_key'] ?? '') : ($provider==='deepseek' ? ($opts['deepseek_key'] ?? '') : ($opts['openai_key'] ?? ''));
    $base = $provider==='groq' ? 'https://api.groq.com/openai/v1' : ($provider==='deepseek' ? 'https://api.deepseek.com/v1' : 'https://api.openai.com/v1');
    $model = $provider==='groq' ? ($opts['model_groq'] ?? 'llama-3.1-70b-versatile') : ($provider==='deepseek' ? ($opts['model_deepseek'] ?? 'deepseek-chat') : ($opts['model_openai'] ?? 'gpt-4o-mini'));
    $headers = [
      'Authorization' => 'Bearer ' . $key,
      'Content-Type'  => 'application/json',
    ];
    // OpenAI Project header if needed
    if ($provider==='openai' && isset($opts['openai_project_id']) && strpos($opts['openai_key'] ?? '', 'sk-proj-')===0) {
      $headers['OpenAI-Project'] = $opts['openai_project_id'];
    }
    $res = wp_remote_post($base.'/chat/completions', [
      'timeout'=>45,
      'headers'=>$headers,
      'body'=> wp_json_encode([
        'model'=>$model,
        'messages'=>[['role'=>'user','content'=>$prompt]],
        'temperature'=>0.7,
      ]),
    ]);
    if (is_wp_error($res)) return '';
    $code = wp_remote_retrieve_response_code($res);
    $body = wp_remote_retrieve_body($res);
    if ($code!==200) return '';
    $data = json_decode($body,true);
    return trim($data['choices'][0]['message']['content'] ?? '');
  } else if ($provider === 'gemini') {
    $key = $opts['gemini_key'] ?? '';
    $model = $opts['model_gemini'] ?? 'gemini-1.5-flash';
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/'.$model.':generateContent?key='.$key;
    $payload = [
      'contents'=>[ ['parts'=>[ ['text'=>$prompt] ] ] ]
    ];
    $res = wp_remote_post($url, [
      'timeout'=>45,
      'headers'=>['Content-Type'=>'application/json'],
      'body'=> wp_json_encode($payload),
    ]);
    if (is_wp_error($res)) return '';
    $code = wp_remote_retrieve_response_code($res);
    $body = wp_remote_retrieve_body($res);
    if ($code!==200) return '';
    $data = json_decode($body,true);
    if (!empty($data['candidates'][0]['content']['parts'][0]['text'])) {
      return trim($data['candidates'][0]['content']['parts'][0]['text']);
    }
    return '';
  }
  return '';
}


  public static function generate_article_and_publish($title, $keywords, $tone, $length, $industry, $opts){
    $openai = $opts['openai_key'] ?? '';
    $unsplash = $opts['unsplash_key'] ?? '';

    if (empty($title)) {
      $title = self::suggest_title_from_keywords($keywords);
    }

    $content = self::multi_generate_article($opts, $title, $keywords, $tone, $length, $industry);
    if (empty($content)) $content = 'Generated content placeholder. Please check your API key/plan.';

    $postarr = [
      'post_title'   => $title,
      'post_content' => wp_kses_post($content),
      'post_status'  => 'draft',
      'post_type'    => 'post'
    ];

    $post_id = wp_insert_post($postarr);

    if ($post_id && !empty($unsplash)) {
      $img_id = self::fetch_unsplash_image($unsplash, $keywords ? $keywords : $industry);
      if ($img_id) set_post_thumbnail($post_id, $img_id);
    }

    // Basic meta description (first 160 chars)
    $meta = wp_strip_all_tags( wp_trim_words($content, 40, '') );
    update_post_meta($post_id, '_ai_bgpro_meta_description', $meta);

    return $post_id;
  }

  public static function generate_bulk_product_descriptions($opts, $limit=20){
    if ( ! class_exists('WC_Product') ) return 0;

    $openai = $opts['openai_key'] ?? '';
    $args = [
      'post_type' => 'product',
      'posts_per_page' => $limit,
      'post_status' => 'publish',
      'meta_query' => [],
    ];

    // Select products with empty content
    $q = new WP_Query($args);
    $processed = 0;
    if ($q->have_posts()){
      foreach ($q->posts as $p){
        $content = get_post_field('post_content', $p->ID);
        if ( trim(strip_tags($content)) === '' ) {
          $new_desc = self::openai_product_description($openai, get_the_title($p->ID));
          if ($new_desc) {
            wp_update_post([
              'ID' => $p->ID,
              'post_content' => wp_kses_post($new_desc)
            ]);
            $processed++;
          }
        }
      }
    }
    return $processed;
  }

  // ---- Helpers ----

  private static function suggest_title_from_keywords($keywords){
    $kw = array_map('trim', explode(',', $keywords));
    $kw = array_filter($kw);
    if (empty($kw)) return 'AI Generated Article';
    return 'How to ' . ucfirst($kw[0]) . ' – Complete Guide';
  }

  private static function openai_generate_article($api_key, $title, $keywords, $tone, $length, $industry){
    if (empty($api_key)) return '';

    $prompt = "Write a high-quality blog post in $tone tone for the $industry industry.\n".
              "Title: $title\n".
              "Keywords: $keywords\n".
              "Length: $length\n".
              "Structure: intro, h2 sections with bullets, conclusion, and a short FAQ.\n".
              "Language: same as the title.";

    $body = [
      'model' => 'gpt-4o-mini', // poți schimba în 3.5-turbo dacă preferi
      'messages' => [
        ['role'=>'system','content'=>'You are a helpful SEO content writer.'],
        ['role'=>'user','content'=>$prompt]
      ],
      'temperature' => 0.7
    ];

    $res = wp_remote_post('https://api.openai.com/v1/chat/completions', [
      'headers' => [
        'Content-Type'  => 'application/json',
        'Authorization' => 'Bearer '.$api_key
      ],
      'timeout' => 60,
      'body'    => wp_json_encode($body)
    ]);

    if (is_wp_error($res)) return '';
    $code = wp_remote_retrieve_response_code($res);
    if ($code !== 200) {
      $err_body = wp_remote_retrieve_body($res);
      $err_msg = '';
      if ($err_body) {
        $err_json = json_decode($err_body, true);
        if (isset($err_json['error']['message'])) $err_msg = $err_json['error']['message'];
      }
      // Fallback model try (older account plans)
      $body['model'] = 'gpt-3.5-turbo-0125';
      $res2 = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'headers' => [
          'Content-Type'  => 'application/json',
          'Authorization' => 'Bearer '.$api_key
        ],
        'timeout' => 60,
        'body'    => wp_json_encode($body)
      ]);
      if (!is_wp_error($res2) && wp_remote_retrieve_response_code($res2) === 200) {
        $data2 = json_decode(wp_remote_retrieve_body($res2), true);
        return $data2['choices'][0]['message']['content'] ?? '';
      }
      if (defined('WP_DEBUG') && WP_DEBUG) {
        return '[OpenAI error] '.($err_msg ?: 'HTTP '.$code);
      }
      return '';
    }

    $data = json_decode(wp_remote_retrieve_body($res), true);
    return $data['choices'][0]['message']['content'] ?? '';
  }

  private static function openai_product_description($api_key, $product_title){
    if (empty($api_key)) return '';

    $prompt = "Write a persuasive WooCommerce product description for: $product_title.\n".
              "Include features, benefits, bullet points, and a clear call-to-action.\n".
              "Max ~180 words. Language: match the product title language.";

    $body = [
      'model' => 'gpt-4o-mini',
      'messages' => [
        ['role'=>'system','content'=>'You are a helpful eCommerce copywriter.'],
        ['role'=>'user','content'=>$prompt]
      ],
      'temperature' => 0.8
    ];

    $res = wp_remote_post('https://api.openai.com/v1/chat/completions', [
      'headers' => [
        'Content-Type'  => 'application/json',
        'Authorization' => 'Bearer '.$api_key
      ],
      'timeout' => 60,
      'body'    => wp_json_encode($body)
    ]);
    if (is_wp_error($res)) return '';
    $code = wp_remote_retrieve_response_code($res);
    if ($code !== 200) {
      $err_body = wp_remote_retrieve_body($res);
      $err_msg = '';
      if ($err_body) {
        $err_json = json_decode($err_body, true);
        if (isset($err_json['error']['message'])) $err_msg = $err_json['error']['message'];
      }
      $body['model'] = 'gpt-3.5-turbo-0125';
      $res2 = wp_remote_post('https://api.openai.com/v1/chat/completions', [
        'headers' => [
          'Content-Type'  => 'application/json',
          'Authorization' => 'Bearer '.$api_key
        ],
        'timeout' => 60,
        'body'    => wp_json_encode($body)
      ]);
      if (!is_wp_error($res2) && wp_remote_retrieve_response_code($res2) === 200) {
        $data2 = json_decode(wp_remote_retrieve_body($res2), true);
        return $data2['choices'][0]['message']['content'] ?? '';
      }
      if (defined('WP_DEBUG') && WP_DEBUG) {
        return '[OpenAI error] '.($err_msg ?: 'HTTP '.$code);
      }
      return '';
    }
    $data = json_decode(wp_remote_retrieve_body($res), true);
    return $data['choices'][0]['message']['content'] ?? '';
  }

  private static function fetch_unsplash_image($api_key, $query){
    $url = add_query_arg([
      'query' => urlencode($query),
      'per_page' => 1,
      'client_id' => $api_key
    ], 'https://api.unsplash.com/search/photos');

    $res = wp_remote_get($url, ['timeout'=>30]);
    if (is_wp_error($res)) return 0;
    if (wp_remote_retrieve_response_code($res) !== 200) return 0;

    $data = json_decode(wp_remote_retrieve_body($res), true);
    $first = $data['results'][0]['urls']['regular'] ?? '';
    if (empty($first)) return 0;

    // Sideload în Media Library
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $tmp = download_url($first, 60);
    if (is_wp_error($tmp)) return 0;

    $file = [
      'name'     => sanitize_file_name( 'ai-bgpro-'.sanitize_title($query).'.jpg' ),
      'type'     => 'image/jpeg',
      'tmp_name' => $tmp,
      'size'     => filesize($tmp),
      'error'    => 0,
    ];
    $id = media_handle_sideload($file, 0);
    if (is_wp_error($id)) return 0;
    return $id;
  }
}


  public static function multi_generate_article($opts, $title, $keywords, $tone, $length, $industry){
    $prompt = "Write a high-quality blog article.
"
            . "Title: {$title}
"
            . "Keywords: {$keywords}
"
            . "Tone: {$tone}
"
            . "Length: {$length}
"
            . "Industry: {$industry}
"
            . "Structure: intro, H2 sections with rich details, conclusion, and FAQs.";
    return self::provider_request($prompt, $opts);
  }

}
