# Sistema de Gestão e Monitoramento de Cólera em Angola

## Visão Geral
Este projeto é uma plataforma web para gestão epidemiológica de casos de cólera em Angola. O sistema permite triagem inteligente, encaminhamento automático de pacientes, gestão de hospitais, ambulâncias, relatórios e monitoramento em tempo real, com foco em apoiar o Ministério da Saúde e profissionais de saúde.

---

## Objetivos
- Triagem inteligente de casos suspeitos de cólera
- Encaminhamento automático para o hospital mais próximo
- Gestão de pacientes, hospitais, ambulâncias e relatórios
- Dashboard e relatórios em tempo real
- Segurança e rastreabilidade das ações

---

## Tecnologias Utilizadas
- **Backend:** Laravel (PHP 8+), API RESTful
- **Frontend:** Angular (ou Vue/React, conforme implementação)
- **Banco de Dados:** MySQL
- **Autenticação:** Laravel Sanctum (JWT)
- **Geolocalização:** Google Maps API
- **Outros:** Chart.js/D3.js (gráficos), Laravel DomPDF (PDF), Laravel Excel (planilhas)

---

## Estrutura do Projeto
```
├── app/
│   ├── Http/Controllers/Api/        # Controllers das APIs
│   ├── Models/                      # Modelos Eloquent
│   ├── Services/                    # Serviços de negócio
│   └── ...
├── database/migrations/             # Migrations do banco
├── routes/api.php                   # Rotas da API
├── storage/logs/                    # Logs do sistema
├── public/                          # Arquivos públicos
├── resources/                       # Views e assets
├── README.md                        # Este arquivo
└── ...
```

---

## Principais Funcionalidades
- **Triagem de Pacientes:** Cadastro, avaliação de sintomas, geração de QR Code, histórico.
- **Encaminhamento Automático:** Pacientes de alto risco são encaminhados para o hospital mais próximo com leito disponível.
- **Gestão de Hospitais:** Cadastro, tipos, leitos, localização.
- **Gestão de Ambulâncias:** Cadastro, status, localização, designação para transportes.
- **Coordenação de Transportes:** Técnicos visualizam pedidos de ambulância e atualizam status.
- **Relatórios e Dashboard:** Estatísticas, gráficos, exportação em PDF/Excel.
- **Logs de Auditoria:** Todas as ações críticas são registradas.

---

## Instalação e Execução
1. **Clone o repositório:**
   ```bash
   git clone <url-do-repositorio>
   cd painel-triagem
   ```
2. **Instale as dependências:**
   ```bash
   composer install
   npm install
   ```
3. **Configure o .env:**
   - Copie `.env.example` para `.env` e configure banco, mail, etc.
   - Gere a chave:
     ```bash
     php artisan key:generate
     ```
4. **Rode as migrations:**
   ```bash
   php artisan migrate
   ```
5. **(Opcional) Popule o banco com seeders:**
   ```bash
   php artisan db:seed
   ```
6. **Inicie o servidor:**
   ```bash
   php artisan serve
   # e para o frontend (exemplo com Vite)
   npm run dev
   ```

---

## Principais Rotas da API
### Autenticação
- `POST /api/login` — Login
- `POST /api/logout` — Logout
- `GET /api/me` — Dados do usuário autenticado

### Pacientes
- `GET /api/pacientes` — Listar pacientes
- `POST /api/pacientes` — Cadastrar paciente
- `GET /api/pacientes/{id}` — Detalhes do paciente

### Triagens
- `GET /api/triagens` — Listar triagens
- `POST /api/triagens` — Cadastrar triagem (encaminhamento automático se risco alto)
- `GET /api/triagens/{id}` — Detalhes da triagem

### Encaminhamentos
- `GET /api/encaminhamentos` — Listar encaminhamentos (filtro por status, hospital, etc)
- `POST /api/encaminhamentos` — Criar encaminhamento manual
- `GET /api/encaminhamentos/{id}` — Detalhes completos (triagem, paciente, hospital, ambulância)
- `PUT /api/encaminhamentos/{id}` — Atualizar status (pendente, em_deslocamento, concluido, cancelado)

### Hospitais
- `GET /api/hospitais` — Listar hospitais (filtro por tipo, leitos, etc)
- `POST /api/hospitais` — Cadastrar hospital
- `GET /api/hospitais/{id}` — Detalhes do hospital

### Ambulâncias
- `GET /api/ambulancias` — Listar ambulâncias
- `GET /api/ambulancias/disponiveis` — Buscar ambulâncias próximas
- `PUT /api/ambulancias/{id}` — Atualizar status da ambulância

### Logs
- `GET /api/logs` — Listar logs do sistema

---

## Exemplo de Fluxo de Triagem e Encaminhamento
1. **Usuário faz login**
2. **Cadastra paciente**
3. **Realiza triagem**
   - Se risco alto, sistema encaminha automaticamente para hospital mais próximo
4. **Técnico visualiza pedidos de transporte**
5. **Técnico designa ambulância e atualiza status**
6. **Gestor acompanha pelo dashboard**

---

## Permissões de Usuário
- **Admin:** Acesso total
- **Médico:** Triagem, cadastro de pacientes
- **Gestor:** Relatórios, gestão de hospitais
- **Técnico:** Gestão de ambulâncias, atualização de status de transportes

---

## Segurança
- Autenticação via JWT (Sanctum)
- Criptografia de dados sensíveis
- Logs de auditoria para todas as ações críticas

---

## Contribuição
1. Faça um fork do projeto
2. Crie uma branch: `git checkout -b minha-feature`
3. Commit suas alterações: `git commit -m 'Minha feature'`
4. Push para o fork: `git push origin minha-feature`
5. Abra um Pull Request

---

## Licença
Este projeto é open-source e está sob a licença MIT.
