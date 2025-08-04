// Produtos de exemplo (poderia vir de um backend)
let products = [
    {
        name: "Camiseta Fragmentado Boxy",
        type: "peça",
        price: 149.99,
        image: "img/fragmentado-costa.jpeg",
        description: "Camiseta boxy com estampa fragmentada.",
        fabric: "algodao",
        recommendation: "Lavar à mão, não usar alvejante, passar em temperatura média."
    },
    {
        name: "Drop Fragmentado",
        type: "conjunto",
        price: 199.90,
        image: "img/fragmentado-frente.jpeg",
        description: "Conjunto fragmentado estiloso.",
        fabric: "poliester",
        recommendation: "Lavar à máquina, secar à sombra, não passar ferro quente."
    }
];

const fabricRecommendations = {
    algodao: "Lavar à mão, não usar alvejante, passar em temperatura média.",
    poliester: "Lavar à máquina, secar à sombra, não passar ferro quente.",
    jeans: "Lavar separadamente, não torcer, secar pendurado."
};

function renderProducts() {
    const tbody = document.querySelector("#productsTable tbody");
    tbody.innerHTML = "";
    products.forEach((prod, idx) => {
        const isEditing = prod.isEditing || false;
        tr = document.createElement("tr");
        tr.innerHTML = `
            <td><input type="file" accept="image/*" class="form-control form-control-sm${!isEditing ? ' non-editable' : ''}" data-idx="${idx}" data-field="image" style="width:110px;display:inline-block;" ${isEditing ? '' : 'disabled'}> <img src="${prod.image}" class="product-img-preview" alt="${prod.name}"></td>
            <td><input type="text" class="form-control form-control-sm${!isEditing ? ' non-editable' : ''}" value="${prod.name}" data-idx="${idx}" data-field="name" ${isEditing ? '' : 'disabled'}></td>
            <td>
                <select class="form-select form-select-sm${!isEditing ? ' non-editable' : ''}" data-idx="${idx}" data-field="type" ${isEditing ? '' : 'disabled'}>
                    <option value="peça" ${prod.type === 'peça' ? 'selected' : ''}>Peça</option>
                    <option value="conjunto" ${prod.type === 'conjunto' ? 'selected' : ''}>Conjunto</option>
                </select>
            </td>
            <td><input type="number" class="form-control form-control-sm${!isEditing ? ' non-editable' : ''}" value="${prod.price}" min="0" step="0.01" data-idx="${idx}" data-field="price" ${isEditing ? '' : 'disabled'}></td>
            <td><textarea class="form-control form-control-sm${!isEditing ? ' non-editable' : ''}" data-idx="${idx}" data-field="description" ${isEditing ? '' : 'disabled'}>${prod.description || ''}</textarea></td>
            <td>
                <select class="form-select form-select-sm${!isEditing ? ' non-editable' : ''}" data-idx="${idx}" data-field="fabric" ${isEditing ? '' : 'disabled'}>
                    <option value="algodao" ${prod.fabric === 'algodao' ? 'selected' : ''}>Algodão</option>
                    <option value="poliester" ${prod.fabric === 'poliester' ? 'selected' : ''}>Poliéster</option>
                    <option value="jeans" ${prod.fabric === 'jeans' ? 'selected' : ''}>Jeans</option>
                </select>
            </td>
            <td><textarea class="form-control form-control-sm${!isEditing ? ' non-editable' : ''}" data-idx="${idx}" data-field="recommendation" ${isEditing ? '' : 'disabled'}>${prod.recommendation || ''}</textarea></td>
            <td>
                <div class="d-flex flex-column gap-2 align-items-center">
                    <button class="btn btn-${isEditing ? 'success' : 'primary'} btn-sm edit-btn w-100 px-3" data-idx="${idx}"><i class="fas fa-${isEditing ? 'check' : 'edit'}"></i> ${isEditing ? 'Salvar' : 'Editar'}</button>
                    <button class="btn btn-danger btn-sm w-100 px-3" data-idx="${idx}"><i class="fas fa-trash"></i> Remover</button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Edição inline dos campos (apenas se estiver editando)
function handleTableEdit(e) {
    const target = e.target;
    const idx = target.getAttribute('data-idx');
    const field = target.getAttribute('data-field');
    if (idx === null || !field) return;
    if (!products[idx].isEditing) return;
    if (field === 'image' && target.files && target.files[0]) {
        products[idx].image = URL.createObjectURL(target.files[0]);
        renderProducts();
        return;
    }
    if (field === 'price') {
        products[idx][field] = parseFloat(target.value);
    } else {
        products[idx][field] = target.value;
    }
    if (field === 'fabric') {
        products[idx].recommendation = fabricRecommendations[target.value];
        renderProducts();
    }
}
document.querySelector('#productsTable').addEventListener('input', handleTableEdit);
document.querySelector('#productsTable').addEventListener('change', handleTableEdit);

// Botão Editar/Salvar
function handleEditButton(e) {
    if (!e.target.closest('.edit-btn')) return;
    const btn = e.target.closest('.edit-btn');
    const idx = btn.getAttribute('data-idx');
    if (!products[idx].isEditing) {
        products[idx].isEditing = true;
        renderProducts();
    } else {
        products[idx].isEditing = false;
        renderProducts();
    }
}
document.querySelector('#productsTable').addEventListener('click', function(e) {
    if (e.target.closest('.edit-btn')) {
        handleEditButton(e);
    } else if (e.target.closest('.btn-danger')) {
        const btn = e.target.closest('.btn-danger');
        const idx = btn.getAttribute('data-idx');
        if (confirm("Tem certeza que deseja remover este produto?")) {
            products.splice(idx, 1);
            renderProducts();
        }
    }
});

// Adicionar produto
document.getElementById("addProductForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const name = document.getElementById("productName").value;
    const type = document.getElementById("productType").value;
    const price = parseFloat(document.getElementById("productPrice").value);
    const imageInput = document.getElementById("productImage");
    const description = document.getElementById("productDescription").value;
    const fabric = document.getElementById("productFabric").value;
    const recommendation = document.getElementById("productRecommendation").value;
    let imageUrl = "";

    // Preview local da imagem (não faz upload real)
    if (imageInput.files && imageInput.files[0]) {
        imageUrl = URL.createObjectURL(imageInput.files[0]);
    } else {
        imageUrl = "img/placeholder.png";
    }

    products.push({ name, type, price, image: imageUrl, description, fabric, recommendation });
    renderProducts();
    this.reset();
    document.getElementById('productRecommendation').value = '';
});

// Recomendações automáticas ao selecionar tecido no formulário
const fabricSelect = document.getElementById('productFabric');
const recommendationField = document.getElementById('productRecommendation');
fabricSelect.addEventListener('change', function() {
    recommendationField.value = fabricRecommendations[this.value];
});

renderProducts();
