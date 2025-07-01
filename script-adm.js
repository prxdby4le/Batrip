// Produtos de exemplo (poderia vir de um backend)
let products = [
    {
        name: "Camiseta Fragmentado Boxy",
        type: "peça",
        price: 149.99,
        image: "img/fragmentado-costa.jpeg"
    },
    {
        name: "Drop Fragmentado",
        type: "conjunto",
        price: 199.90,
        image: "img/fragmentado-frente.jpeg"
    }
];

function renderProducts() {
    const tbody = document.querySelector("#productsTable tbody");
    tbody.innerHTML = "";
    products.forEach((prod, idx) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td><img src="${prod.image}" class="product-img-preview" alt="${prod.name}"></td>
            <td>${prod.name}</td>
            <td>${prod.type.charAt(0).toUpperCase() + prod.type.slice(1)}</td>
            <td>R$ ${prod.price.toFixed(2)}</td>
            <td>
                <button class="btn btn-danger btn-sm" onclick="removeProduct(${idx})"><i class="fas fa-trash"></i> Remover</button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

function removeProduct(idx) {
    if (confirm("Tem certeza que deseja remover este produto?")) {
        products.splice(idx, 1);
        renderProducts();
    }
}

// Adicionar produto
document.getElementById("addProductForm").addEventListener("submit", function(e) {
    e.preventDefault();
    const name = document.getElementById("productName").value;
    const type = document.getElementById("productType").value;
    const price = parseFloat(document.getElementById("productPrice").value);
    const imageInput = document.getElementById("productImage");
    let imageUrl = "";

    // Preview local da imagem (não faz upload real)
    if (imageInput.files && imageInput.files[0]) {
        imageUrl = URL.createObjectURL(imageInput.files[0]);
    } else {
        imageUrl = "img/placeholder.png";
    }

    products.push({ name, type, price, image: imageUrl });
    renderProducts();
    this.reset();
});
