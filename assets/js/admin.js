document.addEventListener('DOMContentLoaded', function(){
  const btnTest = document.getElementById('btnTest');
  const form = document.getElementById('cfgForm'); // Captura o formulário para o efeito de salvar

  // ---------------------------------------------------------
  // 1. LÓGICA DE TESTE (Mesclada: Lógica antiga + Visual Novo)
  // ---------------------------------------------------------
  if (btnTest) {
      btnTest.addEventListener('click', async function(){
          const chatIdInput = document.getElementById('telegram_chat_id');
          const templateInput = document.getElementById('template');

          // Validação simples visual
          if (!chatIdInput || !chatIdInput.value.trim()) {
              Swal.fire({
                  icon: 'warning',
                  title: 'Atenção',
                  text: 'Preencha o Chat ID antes de testar.',
                  confirmButtonColor: '#113f80'
              });
              return;
          }

          // 1. Mostra Loading (Visual Bonito)
          Swal.fire({
              title: 'Enviando teste...',
              text: 'Aguarde a resposta do Telegram',
              allowOutsideClick: false,
              didOpen: () => {
                  Swal.showLoading();
              }
          });

          // 2. Prepara os dados (Lógica Antiga que funciona)
          const formData = new FormData();
          formData.append('action', 'test');
          // Envia os campos da tela
          formData.append('telegram_chat_id', chatIdInput.value);
          formData.append('template', templateInput ? templateInput.value : '');
          
          // Valores simulados (mantendo compatibilidade com seu código antigo)
          formData.append('test_temp', 28.75);
          formData.append('test_hum', 63.4);

          try {
              // 3. Faz a requisição
              const resp = await fetch(window.location.href, {
                  method: 'POST',
                  body: formData,
                  credentials: 'same-origin'
              });

              const json = await resp.json().catch(()=>null);

              // 4. Trata Erro HTTP
              if (!resp.ok) {
                  let errorMsg = json && json.mensagem ? json.mensagem : 'Erro desconhecido';
                  Swal.fire({
                      icon: 'error',
                      title: `Erro HTTP: ${resp.status}`,
                      text: errorMsg,
                      confirmButtonColor: '#df534f'
                  });
                  return;
              }

              // 5. Trata Sucesso ou Falha da API
              if (json) {
                  if (json.ok) {
                      // SUCESSO!
                      // Monta o HTML detalhado para quem quiser ver os detalhes técnicos
                      const detailsHtml = `
                          <div style="text-align: left; font-size: 0.9rem; background: #f8f9fa; padding: 10px; border-radius: 5px; margin-top: 10px;">
                              <strong>HTTP Code:</strong> ${json.http_code}<br>
                              <strong>Resposta API:</strong> ${escapeHtml(String(json.body || ''))}
                              <hr style="margin: 5px 0;">
                              <strong>Mensagem Enviada:</strong><br>
                              <code>${escapeHtml(String(json.sent_message || ''))}</code>
                          </div>
                      `;

                      Swal.fire({
                          icon: 'success',
                          title: 'Enviado com Sucesso!',
                          html: detailsHtml, // Exibe os detalhes formatados
                          width: '600px',    // Um pouco mais largo para caber o log
                          confirmButtonColor: '#113f80'
                      });

                  } else {
                      // FALHA NA API (ex: token inválido, chat id errado)
                      Swal.fire({
                          icon: 'warning',
                          title: 'Falha no Envio',
                          html: `
                              <strong>HTTP:</strong> ${json.http_code}<br>
                              <strong>Erro:</strong> ${escapeHtml(String(json.error || ''))}<br>
                              <span class="text-muted" style="font-size:0.8rem">Body: ${escapeHtml(String(json.body || ''))}</span>
                          `,
                          confirmButtonColor: '#f0ad4e'
                      });
                  }
              } else {
                  // RESPOSTA NÃO JSON
                  Swal.fire({
                      icon: 'error',
                      title: 'Erro Inesperado',
                      text: 'O servidor respondeu algo que não é JSON.',
                      confirmButtonColor: '#df534f'
                  });
              }

          } catch (e) {
              // ERRO DE REDE/JS
              Swal.fire({
                  icon: 'error',
                  title: 'Erro de Rede',
                  text: e.message || String(e),
                  confirmButtonColor: '#df534f'
              });
          }
      });
  }

  // ---------------------------------------------------------
  // 2. FEEDBACK VISUAL AO SALVAR (Formulário)
  // ---------------------------------------------------------
  if (form) {
      form.addEventListener('submit', function() {
          // Mostra um loading rápido antes da página recarregar com o POST
          Swal.fire({
              title: 'Salvando...',
              text: 'Atualizando configurações',
              allowOutsideClick: false,
              didOpen: () => {
                  Swal.showLoading();
              }
          });
      });
  }

  // Função auxiliar para evitar XSS nos logs
  function escapeHtml(s){
      return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
  }
});