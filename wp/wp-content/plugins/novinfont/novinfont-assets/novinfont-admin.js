(function($){'use strict';
var cfg=window.novinfontAdmin||{};
var state={busy:false,currentTab:'dashboard',pickerTarget:null,dirtyTabs:{},tabCache:{},fontLoads:{}};
function q(s,c){return(c||document).querySelector(s)}
function qa(s,c){return Array.prototype.slice.call((c||document).querySelectorAll(s))}
function app(){return q('[data-novinfont-app]')}
function main(){return q('[data-novinfont-main]')}
function label(k,fallback){return(cfg.labels&&cfg.labels[k])||fallback||''}
function parseJson(text){try{return JSON.parse(text);}catch(e){return null;}}
function responseMessage(json,fallback){if(json&&json.data&&json.data.message)return json.data.message;if(json&&typeof json.data==='string')return json.data;return fallback||label('ajaxError','Could not connect to the server. Please try again.');}
function localizedDigits(value){var digits=String(cfg.digits||'0123456789');if(digits.length!==10)digits='0123456789';return String(value==null?'':value).replace(/[0-9]/g,function(d){return digits[Number(d)]||d;});}
function cssLength(value,unit){value=String(value||'').trim();if(!value)return'';return /^-?(?:\d+|\d*\.\d+)$/.test(value)?value+(unit||'px'):value;}
function friendlyPreviewName(name){name=String(name||'').trim();if(!name)return label('selectedFont','Selected font');var clean=name,i;for(i=0;i<3;i++){var next=clean.replace(/(?:[\s_-]*(?:FaNum|FarsiNum|FarsiDigits?|PersianDigits?|FD|FNum|Num|VF|Variable|Web|Desktop|Pro))$/i,'').replace(/[\s_-]+$/,'').trim();if(next===clean)break;clean=next||clean;}return clean.replace(/([a-z])([A-Z])/g,'$1 $2').replace(/[\s_-]+/g,' ').trim()||name;}


function initSelects(scope){
	if(!$.fn.select2)return;
	$(scope||document).find('.novinfont-admin select, select',scope||document).filter(function(){return !!this.closest('.novinfont-admin');}).each(function(){
		var $el=$(this);if($el.hasClass('select2-hidden-accessible')||$el.data('novinfont-select2-ready'))return;
		$el.data('novinfont-select2-ready',1).select2({dir:cfg.isRTL?'rtl':'ltr',width:'100%',dropdownCssClass:'novinfont-select2-dropdown',language:{
			noResults:function(){return label('noMatch','No results found.');},
			searching:function(){return label('searching','Searching...');},
			inputTooShort:function(){return label('inputTooShort','Enter more characters to search.');}
		}});
	});
}
function destroySelects(scope){if(!$.fn.select2)return;$(scope||document).find('select.select2-hidden-accessible').each(function(){try{$(this).select2('destroy');}catch(e){}$(this).removeData('novinfont-select2-ready');});}

function showToast(message,type){
	if(!message)return;qa('[data-novinfont-toast]').forEach(function(t){t.remove();});var root=app();if(!root)return;
	var toast=document.createElement('div');toast.className='novinfont-toast '+(type==='error'?'is-error':'is-success');toast.setAttribute('data-novinfont-toast','');
	toast.innerHTML='<span class="dashicons '+(type==='error'?'dashicons-warning':'dashicons-yes-alt')+'"></span><strong></strong><button type="button" data-novinfont-toast-close aria-label="'+label('close','Close')+'">×</button>';
	q('strong',toast).textContent=message;root.appendChild(toast);setTimeout(function(){if(!toast.parentNode)return;toast.classList.add('is-leaving');setTimeout(function(){if(toast.parentNode)toast.remove();},300);},4200);
}
function anyDirty(){return Object.keys(state.dirtyTabs).some(function(k){return !!state.dirtyTabs[k];});}
function setAjaxState(text,kind){
	var box=q('[data-novinfont-ajax-state]');if(!box)return;box.classList.remove('is-dirty','is-busy','is-ok','is-error');if(kind)box.classList.add('is-'+kind);
	var strong=q('strong',box);if(strong)strong.textContent=text;var icon=q('.dashicons',box);if(icon)icon.className='dashicons '+(kind==='busy'?'dashicons-update':kind==='dirty'?'dashicons-marker':kind==='error'?'dashicons-warning':'dashicons-saved');
}
function refreshDirtyUI(){
	var dirty=anyDirty(),root=app();if(root)root.classList.toggle('has-unsaved',dirty);
	qa('[data-novinfont-tab-link]').forEach(function(link){link.classList.toggle('has-draft',!!state.dirtyTabs[link.getAttribute('data-novinfont-tab-link')]);});
	if(!state.busy)setAjaxState(dirty?label('changesPending','Changes are applied only after you click Save Settings.'):label('allSaved','All changes are saved.'),dirty?'dirty':'ok');
}
function setTabDirty(tab,on){tab=tab||currentTab();if(on)state.dirtyTabs[tab]=true;else{delete state.dirtyTabs[tab];delete state.tabCache[tab];}refreshDirtyUI();}
function markFormDirty(form){if(!form)return;form.setAttribute('data-novinfont-dirty-form','1');setTabDirty(currentTab(),true);}
function setBusy(on,text){
	state.busy=!!on;var root=app();if(root)root.classList.toggle('is-ajax-loading',state.busy);var progress=q('[data-novinfont-ajax-progress]');
	if(progress){progress.hidden=!on;progress.style.setProperty('--novinfont-progress','0%');var em=q('em',progress);if(em)em.textContent=text||label('working','Working...');}
	qa('[data-novinfont-save-all]').forEach(function(save){save.disabled=!!on;});
	if(on)setAjaxState(text||label('working','Working...'),'busy');else refreshDirtyUI();
}
function setProgress(percent,text){var p=q('[data-novinfont-ajax-progress]');if(!p)return;p.hidden=false;p.style.setProperty('--novinfont-progress',Math.max(0,Math.min(100,percent||0))+'%');var em=q('em',p);if(em)em.textContent=text||(label('loading','Loading...')+' '+localizedDigits(Math.round(percent||0))+'%');}

function applyPreviewPayload(data){
	if(!data)return;if(data.previewFamilies)cfg.previewFamilies=data.previewFamilies;if(data.previewNames)cfg.previewNames=data.previewNames;if(data.previewFaces){cfg.previewFaces=data.previewFaces;state.fontLoads={};state.fontBytes={};}
	if(typeof data.fontCss==='string'){var st=q('#novinfont-admin-dynamic-fonts');if(!st){st=document.createElement('style');st.id='novinfont-admin-dynamic-fonts';document.head.appendChild(st);}st.textContent=data.fontCss;}
}
function currentTab(){var m=main();return(m&&m.getAttribute('data-novinfont-tab'))||state.currentTab||'dashboard';}
function updateNav(tab){qa('[data-novinfont-tab-link]').forEach(function(a){a.classList.toggle('is-active',a.getAttribute('data-novinfont-tab-link')===tab);});var root=app();if(root)root.setAttribute('data-novinfont-active-tab',tab);state.currentTab=tab;}
function updateUrl(tab,replace){var u=new URL(window.location.href);u.searchParams.set('page','novinfont');u.searchParams.set('tab',tab);u.searchParams.delete('novinfont_notice');if(replace)history.replaceState({novinfontTab:tab},'',u.toString());else history.pushState({novinfontTab:tab},'',u.toString());}


function captureMainHtml(){
	var src=main();if(!src)return'';var clone=src.cloneNode(true),live=qa('form input,form textarea,form select',src),copy=qa('form input,form textarea,form select',clone);
	for(var i=0;i<Math.min(live.length,copy.length);i++){
		var a=live[i],b=copy[i],tag=a.tagName.toLowerCase(),type=(a.type||'').toLowerCase();
		if(tag==='textarea'){b.textContent=a.value;continue;}
		if(tag==='select'){for(var j=0;j<b.options.length;j++){if(a.options[j]&&a.options[j].selected)b.options[j].setAttribute('selected','selected');else b.options[j].removeAttribute('selected');}continue;}
		if(type==='checkbox'||type==='radio'){if(a.checked)b.setAttribute('checked','checked');else b.removeAttribute('checked');continue;}
		if(type!=='file')b.setAttribute('value',a.value);
	}
	qa('.select2-container',clone).forEach(function(n){n.remove();});qa('select',clone).forEach(function(s){s.classList.remove('select2-hidden-accessible');s.removeAttribute('tabindex');s.removeAttribute('aria-hidden');s.removeAttribute('data-select2-id');});qa('[data-select2-id]',clone).forEach(function(n){n.removeAttribute('data-select2-id');});
	return clone.innerHTML;
}
function stashCurrentTab(){var tab=currentTab();if(!state.dirtyTabs[tab])return;state.tabCache[tab]={html:captureMainHtml(),time:Date.now()};}
function detachedTab(tab){var box=document.createElement('div');box.className='novinfont-admin';box.innerHTML=(state.tabCache[tab]&&state.tabCache[tab].html)||'';return box;}

function loadTab(tab,opts){
	opts=opts||{};if(state.busy&&!opts.force)return Promise.reject(new Error('busy'));if(!opts.skipStash)stashCurrentTab();
	setBusy(true,label('loadingSection','Loading section...'));var body=new URLSearchParams();body.set('action','novinfont_load_tab');body.set('nonce',cfg.ajaxNonce||'');body.set('tab',tab);
	return fetch(cfg.ajaxUrl,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'},body:body.toString()})
	.then(function(r){return r.text();}).then(function(text){
		var json=parseJson(text);if(!json||!json.success)throw new Error(responseMessage(json));var parser=new DOMParser(),doc=parser.parseFromString(json.data.html||'','text/html'),newMain=doc.querySelector('[data-novinfont-main]');if(!newMain)throw new Error(label('sectionMissing','Section content could not be loaded.'));
		var oldMain=main();destroySelects(oldMain);var cached=state.dirtyTabs[tab]&&state.tabCache[tab]&&!opts.forceServer?state.tabCache[tab].html:null;oldMain.innerHTML=cached||newMain.innerHTML;oldMain.setAttribute('data-novinfont-tab',json.data.tab||tab);
		applyPreviewPayload(json.data);updateNav(json.data.tab||tab);initContent(oldMain);refreshDirtyUI();if(opts.history!==false)updateUrl(json.data.tab||tab,!!opts.replace);return json.data;
	}).catch(function(err){showToast(err.message||label('ajaxError'),'error');throw err;}).finally(function(){setBusy(false);});
}

function buildFormData(form,submitter,extra){var fd=new FormData(form);if(submitter&&submitter.name)fd.set(submitter.name,submitter.value||'');var fa=fd.get('novinfont_action');if(fa==='family'&&!fd.has('family_action'))fd.set('family_action','save');fd.set('action','novinfont_admin_action');fd.set('ajax_nonce',cfg.ajaxNonce||'');fd.set('page','novinfont');if(extra)Object.keys(extra).forEach(function(k){fd.set(k,extra[k]);});return fd;}
function ajaxSubmitForm(form,submitter,opts){
	opts=opts||{};if(!form)return Promise.reject(new Error(label('formMissing','Form not found.')));if(!form.checkValidity()){form.reportValidity();return Promise.reject(new Error(label('formIncomplete','Please complete the required form fields.')));}
	var fd=buildFormData(form,submitter,opts.extra),hasFile=!!q('input[type="file"]',form),xhr=new XMLHttpRequest();if(!opts.noBusy)setBusy(true,hasFile?label('uploadingFile','Uploading file...'):label('saving','Saving...'));
	return new Promise(function(resolve,reject){xhr.open('POST',cfg.ajaxUrl,true);xhr.withCredentials=true;if(xhr.upload&&hasFile)xhr.upload.onprogress=function(e){if(e.lengthComputable)setProgress((e.loaded/e.total)*100,label('uploadingFont','Uploading font...')+' '+localizedDigits(Math.round((e.loaded/e.total)*100))+'%');};xhr.onload=function(){var json=parseJson(xhr.responseText);if(!json||!json.success){reject(new Error(responseMessage(json)));return;}applyPreviewPayload(json.data);resolve(json.data||{});};xhr.onerror=function(){reject(new Error(label('ajaxError','Could not connect to the server. Please try again.')));};xhr.send(fd);})
	.then(function(data){if(!opts.silent&&data.message)showToast(data.message,'success');if(opts.reload===false)return data;return loadTab(data.tab||currentTab(),{history:false,force:true,forceServer:true,skipStash:true}).then(function(){return data;});})
	.catch(function(err){if(!opts.silent)showToast(err.message||label('ajaxError'),'error');throw err;}).finally(function(){if(!opts.noBusy)setBusy(false);});
}
function isSaveableForm(form){var a=q('input[name="novinfont_action"]',form);return !!(a&&['save_settings','save_rules','family'].indexOf(a.value)!==-1);}
function saveableForms(scope,dirtyOnly){return qa('form',scope||main()).filter(function(f){return isSaveableForm(f)&&(!dirtyOnly||f.hasAttribute('data-novinfont-dirty-form'));});}
async function saveAll(){
	if(state.busy)return;stashCurrentTab();var tabs=Object.keys(state.dirtyTabs).filter(function(t){return state.dirtyTabs[t];});if(!tabs.length){showToast(label('nothingToSave','There are no changes to save.'),'success');return;}
	setBusy(true,label('savingAll','Saving all changes...'));var savedTabs=[];
	try{
		for(var ti=0;ti<tabs.length;ti++){
			var tab=tabs[ti],scope=tab===currentTab()?main():detachedTab(tab),forms=saveableForms(scope,true);if(!forms.length)forms=saveableForms(scope,false);
			for(var i=0;i<forms.length;i++){if(!forms[i].checkValidity()){forms[i].reportValidity();throw new Error(label('fieldNeedsFix','One or more fields in this section need attention.'));}await ajaxSubmitForm(forms[i],null,{reload:false,silent:true,noBusy:true});}
			savedTabs.push(tab);
		}
		savedTabs.forEach(function(t){delete state.dirtyTabs[t];delete state.tabCache[t];});refreshDirtyUI();setBusy(false);await loadTab(currentTab(),{history:false,force:true,forceServer:true,skipStash:true});showToast(label('savedAll','All settings were saved successfully.'),'success');
	}catch(err){savedTabs.forEach(function(t){delete state.dirtyTabs[t];delete state.tabCache[t];});refreshDirtyUI();showToast(err.message||label('ajaxError'),'error');setBusy(false);}
}

function filterLibrary(){var search=q('[data-novinfont-library-search]'),format=q('[data-novinfont-library-format]'),status=q('[data-novinfont-library-status]'),empty=q('[data-novinfont-library-empty]');var term=(search?search.value:'').toLowerCase().trim(),fmt=format?format.value.toLowerCase():'',st=status?status.value:'',visible=0;qa('.novinfont-font-card',main()).forEach(function(card){var ok=(!term||(card.dataset.name||'').indexOf(term)!==-1)&&(!fmt||(card.dataset.formats||'').indexOf(fmt)!==-1)&&(!st||(card.dataset.status||'').indexOf(st)!==-1);card.hidden=!ok;if(ok)visible++;});if(empty)empty.hidden=visible!==0;}
function previewFamily(id){return cfg.previewFamilies&&cfg.previewFamilies[id]?cfg.previewFamilies[id]:'';}
function previewName(id){return cfg.previewNames&&cfg.previewNames[id]?cfg.previewNames[id]:label('selectedFont','Selected font');}
function previewSentence(){return label('previewSentence','Preview this text using the font above. 1234567890');}
function bestPreviewFace(id,weight,style){
	var faces=cfg.previewFaces&&cfg.previewFaces[id]?cfg.previewFaces[id]:[],wanted=parseInt(weight||400,10)||400,wantedStyle=style||'normal',best=null,bestScore=Infinity;
	faces.forEach(function(face){var score=(face.style===wantedStyle?0:2000);if(face.variable&&wanted>=Number(face.min||100)&&wanted<=Number(face.max||900))score+=0;else score+=Math.abs((Number(face.weight)||400)-wanted);if(score<bestScore){best=face;bestScore=score;}});return best;
}


var NOVINFONT_ARABIC_FORMS={
	'\u0622':['\uFE81','\uFE82',null,null],'\u0627':['\uFE8D','\uFE8E',null,null],'\u0623':['\uFE83','\uFE84',null,null],'\u0625':['\uFE87','\uFE88',null,null],'\u0624':['\uFE85','\uFE86',null,null],
	'\u0626':['\uFE89','\uFE8A','\uFE8B','\uFE8C'],'\u0628':['\uFE8F','\uFE90','\uFE91','\uFE92'],'\u067E':['\uFB56','\uFB57','\uFB58','\uFB59'],'\u0629':['\uFE93','\uFE94',null,null],
	'\u062A':['\uFE95','\uFE96','\uFE97','\uFE98'],'\u062B':['\uFE99','\uFE9A','\uFE9B','\uFE9C'],'\u062C':['\uFE9D','\uFE9E','\uFE9F','\uFEA0'],'\u0686':['\uFB7A','\uFB7B','\uFB7C','\uFB7D'],
	'\u062D':['\uFEA1','\uFEA2','\uFEA3','\uFEA4'],'\u062E':['\uFEA5','\uFEA6','\uFEA7','\uFEA8'],'\u062F':['\uFEA9','\uFEAA',null,null],'\u0630':['\uFEAB','\uFEAC',null,null],
	'\u0631':['\uFEAD','\uFEAE',null,null],'\u0632':['\uFEAF','\uFEB0',null,null],'\u0698':['\uFB8A','\uFB8B',null,null],'\u0633':['\uFEB1','\uFEB2','\uFEB3','\uFEB4'],
	'\u0634':['\uFEB5','\uFEB6','\uFEB7','\uFEB8'],'\u0635':['\uFEB9','\uFEBA','\uFEBB','\uFEBC'],'\u0636':['\uFEBD','\uFEBE','\uFEBF','\uFEC0'],'\u0637':['\uFEC1','\uFEC2','\uFEC3','\uFEC4'],
	'\u0638':['\uFEC5','\uFEC6','\uFEC7','\uFEC8'],'\u0639':['\uFEC9','\uFECA','\uFECB','\uFECC'],'\u063A':['\uFECD','\uFECE','\uFECF','\uFED0'],'\u0641':['\uFED1','\uFED2','\uFED3','\uFED4'],
	'\u0642':['\uFED5','\uFED6','\uFED7','\uFED8'],'\u06A9':['\uFB8E','\uFB8F','\uFB90','\uFB91'],'\u0643':['\uFED9','\uFEDA','\uFEDB','\uFEDC'],'\u06AF':['\uFB92','\uFB93','\uFB94','\uFB95'],
	'\u0644':['\uFEDD','\uFEDE','\uFEDF','\uFEE0'],'\u0645':['\uFEE1','\uFEE2','\uFEE3','\uFEE4'],'\u0646':['\uFEE5','\uFEE6','\uFEE7','\uFEE8'],'\u0647':['\uFEE9','\uFEEA','\uFEEB','\uFEEC'],
	'\u0648':['\uFEED','\uFEEE',null,null],'\u0649':['\uFEEF','\uFEF0',null,null],'\u064A':['\uFEF1','\uFEF2','\uFEF3','\uFEF4'],'\u06CC':['\uFBFC','\uFBFD','\uFBFE','\uFBFF']
};
function novinfontIsTransparentArabic(ch){return /[\u064B-\u065F\u0670\u06D6-\u06ED]/.test(ch||'');}
function shapeArabicPresentation(text){
	var chars=Array.from(String(text||'')),out=[];
	function prevBase(i){for(var j=i-1;j>=0;j--){if(!novinfontIsTransparentArabic(chars[j]))return chars[j];}return'';}
	function nextBase(i){for(var j=i+1;j<chars.length;j++){if(!novinfontIsTransparentArabic(chars[j]))return chars[j];}return'';}
	chars.forEach(function(ch,i){var f=NOVINFONT_ARABIC_FORMS[ch];if(!f){out.push(ch);return;}var pf=NOVINFONT_ARABIC_FORMS[prevBase(i)],nf=NOVINFONT_ARABIC_FORMS[nextBase(i)],joinPrev=!!(pf&&pf[2]&&f[1]),joinNext=!!(f[2]&&nf&&nf[1]);out.push(joinPrev&&joinNext?(f[3]||f[1]||f[0]):joinPrev?(f[1]||f[0]):joinNext?(f[2]||f[0]):f[0]);});
	return out.join('');
}
function previewCanvasHash(family,text){
	try{var c=document.createElement('canvas');c.width=980;c.height=120;var x=c.getContext('2d',{willReadFrequently:true});x.clearRect(0,0,c.width,c.height);x.fillStyle='#111';x.textAlign='right';x.direction='rtl';x.font='42px '+family;x.fillText(text,960,72);var d=x.getImageData(0,0,c.width,c.height).data,h=2166136261,count=0;for(var i=3;i<d.length;i+=16){if(d[i]){h^=(d[i-3]+d[i-2]*3+d[i-1]*7+d[i]*11+i);h=Math.imul(h,16777619);count++;}}return String(h>>>0)+':'+count;}catch(e){return'';}
}
function fontAffectsText(alias,text){var quoted='"'+String(alias).replace(/"/g,'')+'", Arial, sans-serif',base='Arial, sans-serif';return previewCanvasHash(quoted,text)!==previewCanvasHash(base,text);}
function ensurePreviewInk(host){
	if(!host)return null;
	if(host.attachShadow){
		var root=host.shadowRoot;
		if(!root){root=host.attachShadow({mode:'open'});var st=document.createElement('style');st.textContent=':host{display:block!important;contain:paint;} .novinfont-preview-ink{display:block;width:100%;box-sizing:border-box;direction:'+(cfg.isRTL?'rtl':'ltr')+';unicode-bidi:plaintext;text-align:inherit;color:inherit;white-space:normal;word-break:normal;font-synthesis:none!important;text-rendering:optimizeLegibility;} .novinfont-preview-note{display:none;margin-top:7px;color:#9a6b15;font:11px/1.8 Tahoma,Arial,sans-serif;direction:'+(cfg.isRTL?'rtl':'ltr')+';text-align:'+(cfg.isRTL?'right':'left')+'}.novinfont-preview-note.is-visible{display:block}';var ink=document.createElement('span');ink.className='novinfont-preview-ink';var note=document.createElement('small');note.className='novinfont-preview-note';root.appendChild(st);root.appendChild(ink);root.appendChild(note);}
		return root.querySelector('.novinfont-preview-ink');
	}
	return host;
}
function syncPreviewMetrics(host,ink,opts){
	if(!host||!ink)return;opts=opts||{};var cs=getComputedStyle(host),size=opts.fontSize||cs.fontSize||'18px',line=opts.lineHeight||cs.lineHeight||'1.9',color=opts.color||cs.color||'#273145';
	ink.style.setProperty('font-size',size,'important');ink.style.setProperty('line-height',line,'important');ink.style.setProperty('color',color,'important');ink.style.setProperty('font-style',opts.fontStyle||'normal','important');ink.style.setProperty('letter-spacing',opts.letterSpacing!=null?opts.letterSpacing:(cs.letterSpacing||'normal'),'important');ink.style.setProperty('text-transform',opts.textTransform||'none','important');
}
function renderPreviewHost(host,result,text,opts){
	if(!host)return;var ink=ensurePreviewInk(host),raw=String(text||previewSentence());syncPreviewMetrics(host,ink,opts);
	if(!result||!result.family){host.classList.remove('is-font-ready');host.classList.add('is-font-error');ink.textContent=label('previewUnavailable','This font cannot be previewed in the browser.');ink.style.setProperty('font-family','Tahoma, Arial, sans-serif','important');ink.style.setProperty('font-weight','400','important');var errNote=host.shadowRoot&&host.shadowRoot.querySelector('.novinfont-preview-note');if(errNote){errNote.classList.add('is-visible');errNote.textContent=result&&result.error?result.error:label('fontRejected','The browser rejected this font file, so a fake preview is not shown.');}return;}
	var note=host.shadowRoot&&host.shadowRoot.querySelector('.novinfont-preview-note');var shown=result.mode==='legacy'?shapeArabicPresentation(raw):(result.mode==='no-persian'?'ABC abc 1234567890':raw);ink.textContent=shown;ink.style.setProperty('font-family','"'+result.family.replace(/"/g,'')+'"','important');ink.style.setProperty('font-weight',String(result.cssWeight||400),'important');host.classList.add('is-font-ready');host.classList.toggle('is-legacy-preview',result.mode==='legacy');host.classList.toggle('is-no-persian-preview',result.mode==='no-persian');host.classList.remove('is-font-error');if(note){note.classList.toggle('is-visible',result.mode==='legacy'||result.mode==='no-persian');note.textContent=result.mode==='legacy'?label('legacyPreview','This file is shown using compatibility mode for legacy Persian fonts.'):result.mode==='no-persian'?label('latinPreview','This file does not contain the standard Persian characters used in the sample. A real Latin sample is shown instead.'):'';}
}
function novinfontFontSignatureOk(buf,format){
	if(!buf||buf.byteLength<32)return false;
	try{
		var b=new Uint8Array(buf,0,Math.min(buf.byteLength,16)),tag=String.fromCharCode.apply(null,Array.prototype.slice.call(b,0,4)),fmt=String(format||'').toLowerCase();
		if(tag==='OTTO'||tag==='true'||tag==='typ1'||tag==='wOFF'||tag==='wOF2')return true;
		if(b[0]===0x00&&b[1]===0x01&&b[2]===0x00&&b[3]===0x00)return true;
		if(fmt==='eot'||fmt==='embedded-opentype')return buf.byteLength>256;
	}catch(e){}
	return false;
}
function novinfontFetchFontBinary(url,face){
	if(!url)return Promise.reject(new Error(label('fontUrlMissing','The font file URL is missing.')));
	return fetch(url,{credentials:'same-origin',cache:'no-store',redirect:'follow'}).then(function(r){
		if(!r.ok)throw new Error(label('fontFetchFailed','The font file request failed with status %s.').replace('%s',r.status));
		return r.arrayBuffer();
	}).then(function(buf){
		if(!buf||buf.byteLength<128)throw new Error(label('notFontResponse','The received response is not a font file.'));
		if(face&&face.fileSize&&Math.abs(Number(face.fileSize)-buf.byteLength)>16)throw new Error(label('fontSizeMismatch','The received file size does not match the stored font file.'));
		if(!novinfontFontSignatureOk(buf,face&&face.format))throw new Error(label('invalidFontSignature','The server response does not contain a valid font signature.'));
		return buf;
	});
}
function fetchPreviewBytes(face){
	state.fontBytes=state.fontBytes||{};var direct=String(face.directUrl||''),fallback=String(face.url||''),key=String(face.id||fallback)+'|'+direct+'|'+fallback;if(state.fontBytes[key])return state.fontBytes[key];
	state.fontBytes[key]=(direct?novinfontFetchFontBinary(direct,face):Promise.reject(new Error(label('directPathMissing','The direct font path is unavailable.')))).catch(function(primaryError){
		if(!fallback)throw primaryError;
		return novinfontFetchFontBinary(fallback,face).catch(function(){throw primaryError;});
	});
	return state.fontBytes[key];
}
function makeLoadedPreview(alias,face,wanted,buffer){
	var variable=!!face.variable,min=Number(face.min||100),max=Number(face.max||900),cssWeight=variable?String(Math.max(min,Math.min(max,Number(wanted)||400))):'400',desc={style:'normal',weight:variable?(String(min)+' '+String(max)):'400'};
	var ff=new FontFace(alias,buffer,desc);return ff.load().then(function(loaded){document.fonts.add(loaded);return new Promise(function(resolve){requestAnimationFrame(function(){requestAnimationFrame(resolve);});});}).then(function(){var sentence=previewSentence(),mode='unicode';if(!fontAffectsText(alias,sentence)){var legacy=shapeArabicPresentation(sentence);if(legacy!==sentence&&fontAffectsText(alias,legacy))mode='legacy';else mode='no-persian';}return{family:alias,cssWeight:cssWeight,mode:mode,face:face};});
}
function warmPreviewFont(id,weight,style){
	var family=previewFamily(id);if(!family)return Promise.resolve(null);weight=String(weight||400);style=style||'normal';var face=bestPreviewFace(id,weight,style);if(!face||!face.url)return Promise.resolve(null);
	state.fontLoads=state.fontLoads||{};var key=String(face.id||face.url)+'|'+String(face.url)+'|'+weight+'|'+style,alias='novinfont-preview-'+String(face.id||id).replace(/[^a-z0-9_-]/gi,'-')+'-'+String(weight).replace(/\D/g,'');
	if(!state.fontLoads[key])state.fontLoads[key]=fetchPreviewBytes(face).then(function(buf){return makeLoadedPreview(alias,face,weight,buf);}).catch(function(err){return{family:'',error:err&&err.message?err.message:label('previewLoadFailed','The font preview could not be loaded.'),face:face};});return state.fontLoads[key];
}
function updateAB(){
	var a=q('.novinfont-preview-select-a'),b=q('.novinfont-preview-select-b'),range=q('[data-novinfont-preview-weight]'),text=q('[data-novinfont-preview-text]'),pa=q('[data-novinfont-preview-a]'),pb=q('[data-novinfont-preview-b]');if(!pa||!pb)return;
	var wa=range?range.value:'400',custom=text?text.value.trim():'',aid=a?a.value:'',bid=b?b.value:'',sentence=custom||previewSentence(),ha=q('[data-novinfont-preview-copy]',pa),hb=q('[data-novinfont-preview-copy]',pb);var weightOut=q('[data-novinfont-preview-weight-value]');if(weightOut)weightOut.textContent=localizedDigits(wa);
	var tokenA=String(aid)+'|'+wa+'|'+Date.now(),tokenB=String(bid)+'|'+wa+'|'+Date.now();if(ha)ha.dataset.novinfontPreviewToken=tokenA;if(hb)hb.dataset.novinfontPreviewToken=tokenB;
	warmPreviewFont(aid,wa,'normal').then(function(result){if(ha&&ha.dataset.novinfontPreviewToken===tokenA)renderPreviewHost(ha,result,sentence,{fontSize:'24px',lineHeight:'1.95'});});
	warmPreviewFont(bid,wa,'normal').then(function(result){if(hb&&hb.dataset.novinfontPreviewToken===tokenB)renderPreviewHost(hb,result,sentence,{fontSize:'24px',lineHeight:'1.95'});});
}
function updateFamilyCard(controlOrCard){
	var card=controlOrCard&&(controlOrCard.matches&&controlOrCard.matches('.novinfont-font-card')?controlOrCard:controlOrCard.closest&&controlOrCard.closest('.novinfont-font-card'));if(!card)return;
	var sample=q('[data-novinfont-family-preview]',card),nameInput=q('.novinfont-family-name',card),familyId=card.getAttribute('data-novinfont-family-id')||'',name=(nameInput&&nameInput.value||'').trim()||label('selectedFont','Selected font'),displayName=friendlyPreviewName(name),currentWeight=sample?(sample.dataset.novinfontPreviewWeight||sample.style.getPropertyValue('--novinfont-preview-weight')||'400'):'400',currentStyle=sample?(sample.dataset.novinfontPreviewStyle||sample.style.getPropertyValue('--novinfont-preview-style')||'normal'):'normal';
	if(controlOrCard&&controlOrCard.name&&/\[weight\]$/.test(controlOrCard.name))currentWeight=controlOrCard.value||'400';if(controlOrCard&&controlOrCard.name&&/\[style\]$/.test(controlOrCard.name))currentStyle=controlOrCard.value||'normal';
	if(sample){sample.dataset.novinfontPreviewWeight=currentWeight;sample.dataset.novinfontPreviewStyle=currentStyle;var token=familyId+'|'+currentWeight+'|'+currentStyle+'|'+Date.now();sample.dataset.novinfontPreviewToken=token;warmPreviewFont(familyId,currentWeight,currentStyle).then(function(result){if(sample.dataset.novinfontPreviewToken===token)renderPreviewHost(sample,result,previewSentence(),{fontSize:'19px',lineHeight:'2.1',fontStyle:currentStyle});});}
	if(familyId){cfg.previewNames=cfg.previewNames||{};cfg.previewNames[familyId]=displayName;}
	var familyEnabled=q('input[name="family_enabled"]',card);card.classList.toggle('is-preview-disabled',!!familyEnabled&&!familyEnabled.checked);
	qa('.novinfont-face',card).forEach(function(face){var enabled=q('input[name$="[enabled]"]',face),preload=q('input[name$="[preload]"]',face);face.classList.toggle('is-preview-disabled',!!enabled&&!enabled.checked);face.classList.toggle('is-preview-preload',!!preload&&preload.checked);});
}
function updateRulePreview(rule){
	if(!rule)return;var surface=q('[data-novinfont-rule-preview-surface]',rule),meta=q('[data-novinfont-rule-preview-meta]',rule);if(!surface)return;
	var family=q('.novinfont-rule-family',rule),weight=q('select[name*="[weight]"]',rule),style=q('select[name*="[style]"]',rule),line=q('input[name*="[line_height]"]',rule),spacing=q('input[name*="[letter_spacing]"]',rule),size=q('input[name*="[size][desktop]"]',rule),transform=q('select[name*="[transform]"]',rule),enabled=q('input[name*="[enabled]"]',rule),labelInput=q('.novinfont-rule-label',rule),selector=q('[data-novinfont-selector]',rule),fid=family?family.value:'',w=weight&&weight.value?weight.value:'400',st=style&&style.value?style.value:'normal',opts={lineHeight:line&&line.value?line.value:'1.9',letterSpacing:spacing&&spacing.value?cssLength(spacing.value,'px'):'0',fontSize:size&&size.value?cssLength(size.value,'px'):'18px',textTransform:transform&&transform.value&&transform.value!=='none'?transform.value:'none',fontStyle:st};
	surface.style.opacity=enabled&&!enabled.checked?'.42':'1';var token=fid+'|'+w+'|'+st+'|'+Date.now();surface.dataset.novinfontPreviewToken=token;warmPreviewFont(fid,w,st).then(function(result){if(surface.dataset.novinfontPreviewToken===token)renderPreviewHost(surface,result,previewSentence(),opts);});
	if(meta){var bits=[];if(labelInput&&labelInput.value.trim())bits.push(labelInput.value.trim());if(selector&&selector.value.trim())bits.push(selector.value.trim());bits.push(fid?previewName(fid):label('defaultFont','Default font'));if(weight&&weight.value)bits.push(label('weight','Weight')+' '+localizedDigits(weight.value));bits.push(label('notSaved','Not saved'));meta.textContent=bits.join(' · ');}
}
function previewLocalFileInput(input){
	var box=q('[data-novinfont-local-file-preview]'),surface=q('[data-novinfont-local-preview-surface]'),status=q('[data-novinfont-local-preview-status]');if(!box||!surface)return;var file=input&&input.files&&input.files[0];if(!file){box.hidden=true;return;}box.hidden=false;if(status)status.textContent=label('buildingPreview','Building a real font preview...');
	file.arrayBuffer().then(function(buf){var alias='novinfont-local-'+Date.now(),fake={id:alias,variable:false,min:400,max:400};return makeLoadedPreview(alias,fake,400,buf);}).then(function(result){renderPreviewHost(surface,result,previewSentence(),{fontSize:'21px',lineHeight:'2'});if(status)status.textContent=result&&result.mode==='legacy'?label('legacyFilePreview','This font is being previewed using legacy Persian font compatibility mode.'):result&&result.mode==='no-persian'?label('noPersianGlyphs','The file loads correctly, but it does not contain the standard Persian characters used in this sample.'):label('directFilePreview','The preview is rendered directly from the selected file.');}).catch(function(){renderPreviewHost(surface,null,previewSentence(),{fontSize:'21px',lineHeight:'2'});if(status)status.textContent=label('browserCannotRender','The browser could not render this file as a web font.');});
}
function updateToggleState(control){var row=control&&control.closest&&control.closest('.novinfont-toggle-row');if(row)row.classList.toggle('is-live-on',!!control.checked);}
function updateLocalPreview(control){
	if(!control)return;if(control.matches('.novinfont-preview-select-a,.novinfont-preview-select-b,[data-novinfont-preview-weight],[data-novinfont-preview-text]'))updateAB();
	var card=control.closest&&control.closest('.novinfont-font-card');if(card)updateFamilyCard(control);
	var rule=control.closest&&control.closest('[data-novinfont-rule]');if(rule)updateRulePreview(rule);
	if(control.matches&&control.matches('input[type="checkbox"]'))updateToggleState(control);
	var panel=control.closest&&control.closest('.novinfont-card,.novinfont-font-card,.novinfont-rule');if(panel&&control.closest('form')&&isSaveableForm(control.closest('form')))panel.classList.add('has-live-draft');
}
function initContent(scope){scope=scope||document;initSelects(scope);updateAB();filterLibrary();qa('.novinfont-font-card',scope).forEach(updateFamilyCard);qa('[data-novinfont-rule]',scope).forEach(updateRulePreview);qa('.novinfont-toggle-row input[type="checkbox"]',scope).forEach(updateToggleState);}
function addRule(){var rules=q('[data-novinfont-rules]'),tpl=q('#novinfont-rule-template');if(!rules||!tpl)return;var index=Date.now(),id='rule_'+index,html=tpl.innerHTML.replace(/__INDEX__/g,index).replace(/__ID__/g,id),wrap=document.createElement('div');wrap.innerHTML=html,added=[];while(wrap.firstElementChild){var n=wrap.firstElementChild;rules.appendChild(n);added.push(n);}added.forEach(function(n){initSelects(n);});var form=q('[data-novinfont-rules-form]');markFormDirty(form);var last=rules.lastElementChild;if(last)last.scrollIntoView({behavior:'smooth',block:'center'});}
document.addEventListener('click',function(e){
	var close=e.target.closest('[data-novinfont-toast-close]');if(close){var toast=close.closest('[data-novinfont-toast]');if(toast)toast.remove();return;}
	var save=e.target.closest('[data-novinfont-save-all]');if(save){e.preventDefault();saveAll();return;}
	var familyToggle=e.target.closest('button[name="family_action"][value="toggle"]');if(familyToggle){e.preventDefault();var familyForm=familyToggle.closest('form'),familySwitch=familyForm&&q('input[name="family_enabled"]',familyForm);if(familySwitch){familySwitch.checked=!familySwitch.checked;familySwitch.dispatchEvent(new Event('change',{bubbles:true}));familyToggle.textContent=familySwitch.checked?label('disable','Disable'):label('enable','Enable');}return;}
	var nav=e.target.closest('a[href]');if(nav&&nav.closest('.novinfont-admin')){try{var u=new URL(nav.href,window.location.href),tab=u.searchParams.get('tab');if(u.searchParams.get('page')==='novinfont'&&tab){e.preventDefault();if(tab===currentTab())return;loadTab(tab,{history:true});return;}}catch(err){}}
	var open=e.target.closest('[data-novinfont-upload-open]');if(open){e.preventDefault();var panel=q('[data-novinfont-upload-panel]');if(panel){panel.classList.toggle('is-open');if(panel.classList.contains('is-open'))panel.scrollIntoView({behavior:'smooth',block:'nearest'});}return;}
	var del=e.target.closest('[data-novinfont-confirm-delete]');if(del&&!window.confirm(label('confirmDelete','Are you sure you want to delete this item?'))){e.preventDefault();e.stopImmediatePropagation();return;}
	var add=e.target.closest('[data-novinfont-add-rule]');if(add){e.preventDefault();addRule();return;}
	var remove=e.target.closest('[data-novinfont-remove-rule]');if(remove){e.preventDefault();var row=remove.closest('[data-novinfont-rule]');if(row&&window.confirm(label('confirmRule','Delete this typography rule?'))){var form=row.closest('form');destroySelects(row);row.remove();markFormDirty(form);}return;}
	var picker=e.target.closest('[data-novinfont-picker]');if(picker){e.preventDefault();var row2=picker.closest('[data-novinfont-rule]');state.pickerTarget=q('[data-novinfont-selector]',row2);window.open(cfg.pickerUrl,'novinfontSelectorPicker','width=1280,height=800,resizable=yes,scrollbars=yes');return;}
});
document.addEventListener('click',function(e){var b=e.target.closest('form .button,form button,form input[type="submit"]');if(b&&b.form)b.form._novinfontSubmitter=b;},true);
document.addEventListener('submit',function(e){
	var form=e.target;if(!form.closest||!form.closest('.novinfont-admin'))return;e.preventDefault();if(state.busy)return;var submitter=e.submitter||form._novinfontSubmitter||null;form._novinfontSubmitter=null;
	var action=q('input[name="novinfont_action"]',form),av=action?action.value:'';if(['save_settings','save_rules'].indexOf(av)!==-1){markFormDirty(form);showToast(label('draftSavedLocally','Changes are kept as a draft. Click Save Settings to apply them to the site.'),'success');return;}
	if(av==='family'){
		var familyAction=submitter&&submitter.name==='family_action'?submitter.value:'save';if(familyAction==='save'){markFormDirty(form);showToast(label('familyDraft','Family changes are not saved yet. Click Save Settings to apply them to the site.'),'success');return;}
	}
	ajaxSubmitForm(form,submitter);
});
document.addEventListener('change',function(e){
	if(e.target.matches('[data-novinfont-files]')){var lab=q('[data-novinfont-files-label]',e.target.closest('form'));if(lab)lab.textContent=e.target.files.length?label('filesSelected','%s files selected').replace('%s',localizedDigits(e.target.files.length)):label('noFileSelected','No file selected yet.');previewLocalFileInput(e.target);return;}
	if(e.target.matches('[data-novinfont-library-format],[data-novinfont-library-status]'))filterLibrary();updateLocalPreview(e.target);
	var f=e.target.closest('form');if(f&&f.closest('.novinfont-main')&&isSaveableForm(f))markFormDirty(f);
});
document.addEventListener('input',function(e){
	if(e.target.matches('[data-novinfont-library-search]'))filterLibrary();updateLocalPreview(e.target);
	var f=e.target.closest&&e.target.closest('form');if(f&&f.closest('.novinfont-main')&&isSaveableForm(f))markFormDirty(f);
});

$(document).on('change.novinfontLive select2:select.novinfontLive select2:clear.novinfontLive', '.novinfont-admin select', function(){updateLocalPreview(this);var f=this.closest('form');if(f&&f.closest('.novinfont-main')&&isSaveableForm(f))markFormDirty(f);});
document.addEventListener('dragenter',function(e){var z=e.target.closest&&e.target.closest('.novinfont-dropzone');if(z){e.preventDefault();z.classList.add('is-drag');}});
document.addEventListener('dragover',function(e){var z=e.target.closest&&e.target.closest('.novinfont-dropzone');if(z)e.preventDefault();});
document.addEventListener('dragleave',function(e){var z=e.target.closest&&e.target.closest('.novinfont-dropzone');if(z)z.classList.remove('is-drag');});
document.addEventListener('drop',function(e){var z=e.target.closest&&e.target.closest('.novinfont-dropzone');if(z){e.preventDefault();z.classList.remove('is-drag');}});
window.addEventListener('message',function(e){if(cfg.homeOrigin&&e.origin!==cfg.homeOrigin)return;var d=e.data||{};if(d.type==='novinfont-selector-picked'&&state.pickerTarget){state.pickerTarget.value=d.selector||'';state.pickerTarget.dispatchEvent(new Event('input',{bubbles:true}));state.pickerTarget.classList.add('is-flash');var target=state.pickerTarget;setTimeout(function(){if(target)target.classList.remove('is-flash');},1000);state.pickerTarget=null;}});
window.addEventListener('popstate',function(){var u=new URL(window.location.href),tab=u.searchParams.get('tab')||'dashboard';if(tab!==currentTab())loadTab(tab,{history:false,force:true});});
window.addEventListener('beforeunload',function(e){if(!anyDirty())return;e.preventDefault();e.returnValue='';});
state.currentTab=currentTab();initContent(document);refreshDirtyUI();
})(window.jQuery);
