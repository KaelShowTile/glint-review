// Function to extract hash parameters from the URL
function getHashParam(param) 
{
    const hash = window.location.hash.substring(1); // Remove the '#' symbol
    const hashParams = new URLSearchParams(hash);
    let decoded = decodeURIComponent(hashParams.get(param));
    decoded = decoded.replace(/\+/g, ' ');
    return decoded;
}

const starRating = document.querySelector('.star-rating');
const ratingValue = document.getElementById('rating-value');

starRating.addEventListener('change', function(e) 
{
    ratingValue.textContent = e.target.value;
});

// Set the values of the fields from the URL parameters
const productIdInput = document.getElementById('product_id');
const customerNameInput = document.getElementById('customer_name');
const customerEmailInput = document.getElementById('customer_email');
const productReviewInput = document.getElementById('product_review');

// Extract parameters from the URL
const productId = getHashParam('product_id');
const productName = getHashParam('product_name');
const productLink = getHashParam('product_link');
const customerName = getHashParam('customer_name');
const customerEmail = getHashParam('customer_email');

// Populate the fields with the extracted values     
if (productId) 
{
    productIdInput.value = productId;
} 
else 
{
    console.log('No product_id found.');
}

if (productName) 
{
    const productTitleElement = document.getElementById('product-title');
    productTitleElement.textContent = productName;

    if(productLink)
    {
        const linkElement = document.getElementById('product-link'); 
        linkElement.href = productLink; 
    }
} 

if (customerName) 
{
    customerNameInput.value = customerName;
} 

if (customerEmail) 
{
    customerEmailInput.value = customerEmail;
} 


let selectedFiles = [];

function displayThumbnails() 
{
    const thumbnailContainer = document.getElementById('thumbnailContainer');
    thumbnailContainer.innerHTML = ''; 

    selectedFiles.forEach((file, index) => {
        const thumbnail = document.createElement('div');
        thumbnail.className = 'thumbnail';

        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.alt = file.name;

        const removeIcon = document.createElement('div');
        removeIcon.className = 'remove-icon';
        removeIcon.innerHTML = '×';
        removeIcon.onclick = () => removeImage(index);

        thumbnail.appendChild(img);
        thumbnail.appendChild(removeIcon);

        thumbnailContainer.appendChild(thumbnail);
    });
}

// Function to remove an image from the selection
function removeImage(index) 
{
    selectedFiles.splice(index, 1); 
    displayThumbnails(); 
    updateFileInput(); 
}

// Function to update the file input with the selected files
function updateFileInput() 
{
    const fileInput = document.getElementById('images');
    const dataTransfer = new DataTransfer();

    selectedFiles.forEach(file => dataTransfer.items.add(file));
    fileInput.files = dataTransfer.files;
}


// Function to handle file selection
document.getElementById('images').addEventListener('change', function (e) 
{
    const files = Array.from(e.target.files);

    // Add new files to the selectedFiles array
    selectedFiles = [...selectedFiles, ...files].slice(0, 9); // Limit to 9 files

    displayThumbnails();
    updateFileInput();
});

// Function to generate a unique filename
function generateUniqueFileName(customerName, orderNumber) {
    const now = new Date();
    const dateTime = now.toISOString().replace(/[-:]/g, '').replace('T', '_').split('.')[0]; // Format: YYYYMMDD_HHMMSS
    return `${customerName}_${dateTime}_${orderNumber}.webp`;
}


// Function to compress images using browser-image-compression
async function compressImage(file, maxWidth, maxHeight, maxSizeMB = 1, customerName, orderNumber) 
{
    const options = {
        maxSizeMB: maxSizeMB, // Maximum file size in MB
        maxWidthOrHeight: Math.max(maxWidth, maxHeight), // Maximum dimension
        useWebWorker: true, // Use a web worker for better performance
        fileType: 'image/webp', // Convert to WebP format
    };

    try 
    {
        const compressedFile = await imageCompression(file, options);

        // Generate a unique filename
        const uniqueFileName = generateUniqueFileName(customerName, orderNumber);

        // Create a new File object with the unique filename
        const renamedFile = new File([compressedFile], uniqueFileName, {
            type: 'image/webp',
            lastModified: Date.now(),
        });

        return renamedFile;
    } catch (error) 
    {
        console.error('Error compressing image:', error);
        throw error;
    }
}

// Update the form submission handler
document.getElementById('uploadForm').addEventListener('submit', async function (e) 
{
    e.preventDefault();

    const productRating = document.getElementById('rating-value').textContent;

    if(productRating == 0)
    {
        productRating = 5;
    } 

    const compressedFiles = [];

    try {
        
        if (selectedFiles.length > 0)
        {
            for (let i = 0; i < selectedFiles.length; i++) {
                const file = selectedFiles[i];
                console.log('Processing file:', file.name);
                const compressedFile = await compressImage(file, 800, 600, 1, customerNameInput.value, i + 1); // Max size: 1MB
                compressedFiles.push(compressedFile);
            }

            console.log('All files compressed and renamed successfully:', compressedFiles);
        }

        const formData = new FormData();

        compressedFiles.forEach((file, index) => {
            formData.append(`image${index + 1}`, file, file.name);
        });

        formData.append('product_id', productIdInput.value);
        formData.append('customer_name', customerNameInput.value);
        formData.append('customer_email', customerEmailInput.value);
        formData.append('product_review', productReviewInput.value);
        formData.append('product_rating', productRating);

        console.log('Form data prepared:', formData);

        const response = await fetch('review-submit.php', {
            method: 'POST',
            body: formData,
        });

        if (!response.ok) {
            throw new Error(`Server returned ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();
        alert('We received your review. Thank you for rating our product!');
        window.location.href = productLink;

    } catch (error) {
        console.error('Error during form submission:', error);
    }
});