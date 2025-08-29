(function(){
  const root=document.getElementById('wpp-settings'); if(!root) return;
  root.innerHTML=`<div class="card">
    <h3>Global Eligibility Defaults</h3>
    <label>Quiet start <input id="qs" value="22:00"/></label>
    <label>Quiet end <input id="qe" value="08:00"/></label>
    <label>TZ Mode <select id="tz"><option value="site">Site</option><option value="contact">Contact</option></select></label>
    <label>Freq cap max <input id="fcmax" type="number" value="0"/></label>
    <label>Freq window <select id="fcw"><option>P1D</option><option>P7D</option><option>P30D</option></select></label>
    <button id="save" class="button button-primary">Save</button>
  </div>`;
  const set=(v)=>{ if(!v) return; if(v.quiet_hours){qs.value=v.quiet_hours.start||'22:00'; qe.value=v.quiet_hours.end||'08:00';} tz.value=v.tz_mode||'site'; fcmax.value=(v.freq_cap&&v.freq_cap.max)||0; fcw.value=(v.freq_cap&&v.freq_cap.window)||'P1D'; };
  fetch(`${WPP_SETTINGS.root}/settings`,{headers:{'X-WP-Nonce':WPP_SETTINGS.nonce}}).then(r=>r.json()).then(set);
  document.getElementById('save').onclick=()=>{
    const body={ quiet_hours:{start:val('#qs'),end:val('#qe')}, tz_mode:val('#tz'), freq_cap:{max:+val('#fcmax'), window:val('#fcw')} };
    fetch(`${WPP_SETTINGS.root}/settings`,{method:'POST',headers:{'X-WP-Nonce':WPP_SETTINGS.nonce,'Content-Type':'application/json'},body:JSON.stringify(body)}).then(()=>alert('Saved'));
  };
  function val(s){return root.querySelector(s).value;}
})();