# Galeria de Imagens do Produto (Admin)

Este módulo permite ao administrador gerenciar múltiplas imagens por produto:

- Adicionar diversas imagens via modal com drag & drop ou seleção por clique
- Reordenar a galeria arrastando os itens
- Definir uma imagem como Principal
- Remover imagens (removendo também o arquivo físico quando salvo em `uploads/products`)

## Telas

- Criação (`/adm/produtos/novo`): botão "Adicionar imagens" abre o modal; as imagens ficam em fila e são enviadas junto ao formulário.
- Edição (`/adm/produtos/{id}/editar`): mostra galeria atual com opções de reordenar, definir principal e remover. O modal envia as imagens imediatamente (AJAX).

## Endpoints (Admin)

- POST `/adm/produtos/{id}/imagens/upload` — upload múltiplo (campo `images[]`)
- POST `/adm/produtos/{id}/imagens/reordenar` — reordena; enviar `order[]` com IDs em ordem
- POST `/adm/produtos/{id}/imagens/{imageId}/remover` — remove imagem
- POST `/adm/produtos/{id}/imagens/{imageId}/principal` — define imagem principal

Respostas em JSON têm forma `{ success: boolean, ... }` e, quando aplicável, retornam `images` (lista atualizada).

## Armazenamento

- Arquivos são salvos em `uploads/products/` com prefixo `p{productId}-...`.
- Registros são gravados na tabela `product_images` (campos: `id`, `product_id`, `url`, `position`, `is_primary`).
- O campo `products.image` é mantido sincronizado com a imagem principal para compatibilidade.

## Limites e validações

Configurações em `config/config.php`:

- `IMAGES_PER_PRODUCT_MAX` (padrão: 12)
- `IMAGE_MAX_UPLOAD_MB` (padrão: 5 MB)

Tipos aceitos: `image/jpeg`, `image/png`, `image/webp`.

## Observações

- Ao remover a imagem principal, o sistema elege a primeira imagem restante como principal; se não houver imagens, o campo `products.image` é limpo.
- As views públicas usam `product-image.php?id={id}` que faz fallback para a URL salva em `products.image` quando não há blob.
