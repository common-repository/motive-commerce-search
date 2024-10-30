(function(){"use strict";function u(t){const e=document.createElement("meta");e.id="motive_cart_info",e.dataset.productsCount="0",document.head.appendChild(e),document.addEventListener("DOMContentLoaded",function(){typeof jQuery>"u"||jQuery(document.body).on("wc_fragments_loaded",function(){jQuery(document.body).trigger("wc_fragment_refresh")})}),t.initParams.callbacks={UserClickedResultAddToCart:m,CartHandlerGettingCartInfo:f}}function f(){return{productsCount:parseInt(motive_cart_info.dataset.productsCount||"0")}}function m(t){if(!t.url)return;const e=new FormData;return e.append("quantity","1"),e.append("add-to-cart",t.id.toString()),fetch(t.url,{method:"POST",body:e}).then(()=>{motive_cart_info.dataset.productsCount=(parseInt(motive_cart_info.dataset.productsCount)+1).toString(),!(typeof jQuery>"u")&&jQuery(document.body).trigger("wc_fragment_refresh")})}let c=1,d=1;function p(t){if(t.options.shopperPrices){let e=function(r){const a=r.map(o=>o.variants?{id:o.id,variants:o.variants.map(h=>({id:h.id}))}:{id:o.id}),s=new URL(t.endpoints.shopperPrices);return s.searchParams.append("nonce",motiveShopperPricesNonce),fetch(s,{method:"PATCH",body:JSON.stringify(a)}).then(o=>o.json())};t.initParams.callbacks.AppendedResultsChanged=e,t.initParams.callbacks.RecommendationsChanged=e,t.initParams.transformPriceRange=(r,a)=>[r*c,a*d];const n=new URL(t.endpoints.shopperPrices);n.searchParams.append("nonce",motiveShopperPricesNonce),n.searchParams.append("action","price_rates"),fetch(n,{method:"PATCH"}).then(r=>r.json()).then(r=>[c,d]=r)}}function l(t){Array.from(document.querySelectorAll(t)).map(function(e){return e.closest("form")||e.querySelector("form")}).filter(function(e,n,r){return e&&r.indexOf(e)===n}).forEach(function(e){e&&(e.onsubmit=function(){return!1})})}/*
 * (C) 2023 Motive Commerce Search Corp S.L. <info@motive.co>
 *
 * This file is part of Motive Commerce Search.
 *
 * This file is licensed to you under the Apache License, Version 2.0 (the 'License');
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an 'AS IS' BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author Motive (motive.co)
 * @copyright (C) 2023 Motive Commerce Search Corp S.L. <info@motive.co>
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */typeof motive<"u"?i(motive):document.addEventListener("DOMContentLoaded",function(){if(typeof motive<"u")i(motive);else{const t=document.querySelector("#motive-config-url");if(!t)return;fetch(t.href,{method:"GET",credentials:"include",mode:"no-cors"}).then(e=>e.json()).then(e=>i(e))}});function i(t){if(u(t),p(t),window.initX=function(){return l(t.initParams.triggerSelector),t.initParams},{}.VITE_BUILD_TYPE!=="NOSCRIPT"){const e=document.querySelector("#motive-layer-js").href,n=document.createElement("script");n.setAttribute("src",e),n.setAttribute("type","module"),document.head.appendChild(n)}}})();
