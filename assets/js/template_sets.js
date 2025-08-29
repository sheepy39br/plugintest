(function(){
  const root=document.getElementById('wpp-tsets'); if(!root) return;
  root.innerHTML=`<div class="card">
    <label>Name <input id="name"/></label>
    <label>Variants (JSON) <textarea id="variants" rows="6">[{"template_id":1,"label":"A","weight":50},{"template_id":2,"label":"B","weight":50}]</textarea></label>
    <button id="save" class="button button-primary">Save Set</button>
  </div>`;
  document.getElementById('save').onclick=async()=>{
    const variants = JSON.parse(document.getElementById('variants').value||'[]'); const name=document.getElementById('name').value||('Set '+Date.now());
    const res = await fetch(`${WPP_TSETS.root}/templates`,{headers:{'X-WP-Nonce':WPP_TSETS.nonce}}).then(r=>r.json());
    // store in table sets
    const r = await fetch(`${WPP_TSETS.root}/templates`,{method:'POST',headers:{'X-WP-Nonce':WPP_TSETS.nonce,'Content-Type':'application/json'},body:JSON.stringify({name:name, language:'en_US', body:'AB holder'})}).then(r=>r.json());
    alert('Saved placeholder template id '+r.id+' (folosește UI Templates într-o versiune ulterioară pentru a adăuga real sets).');
  };
})();