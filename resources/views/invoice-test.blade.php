<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Modal Test</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Oswald:wght@700&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Invoice Modal Test</h1>
        
        <button onclick="openModal()" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700">
            Open Modal
        </button>

        <!-- Modal -->
        <div id="testModal" class="hidden fixed inset-0 z-[9999] overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20">
                <!-- Background Overlay -->
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" onclick="closeModal()"></div>

                <!-- Modal Content -->
                <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full p-6 z-10">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-bold">Invoice Preview</h2>
                        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Invoice Content -->
                    <div id="invoice-content" class="bg-white p-8 border-2 border-gray-300">
                        <h1 class="text-5xl font-bold mb-4" style="font-family: 'Oswald', sans-serif;">INVOICE</h1>
                        
                        <div class="mb-6">
                            <p class="font-bold">John Smith</p>
                            <p>PNE Maintenance</p>
                            <p>Maintenance Services</p>
                        </div>

                        <div class="grid grid-cols-3 gap-4 mb-6">
                            <div>
                                <h3 class="font-bold text-sm mb-2">BILL TO</h3>
                                <p class="font-bold">Store #005</p>
                                <p class="text-sm">Mall Road</p>
                            </div>
                            <div>
                                <h3 class="font-bold text-sm mb-2">INVOICE #</h3>
                                <p class="font-semibold">INT-0001</p>
                            </div>
                            <div>
                                <h3 class="font-bold text-sm mb-2">DATE</h3>
                                <p class="font-semibold">12/05/2025</p>
                            </div>
                        </div>

                        <table class="w-full mb-6">
                            <thead class="border-t-2 border-b-2 border-red-600">
                                <tr>
                                    <th class="text-left py-2">DESCRIPTION</th>
                                    <th class="text-right py-2">AMOUNT</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="border-b">
                                    <td class="py-2">Labor - 8 hours @ $50/hr</td>
                                    <td class="text-right py-2">$400.00</td>
                                </tr>
                                <tr class="border-b">
                                    <td class="py-2">Materials</td>
                                    <td class="text-right py-2">$150.00</td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="flex justify-end">
                            <div class="w-64">
                                <div class="flex justify-between py-2">
                                    <span>Subtotal</span>
                                    <span>$550.00</span>
                                </div>
                                <div class="flex justify-between py-2">
                                    <span>Tax (5%)</span>
                                    <span>$27.50</span>
                                </div>
                                <div class="flex justify-between py-2 border-t-2 border-gray-800 font-bold text-lg">
                                    <span>TOTAL</span>
                                    <span>$577.50</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Download Button -->
                    <div class="mt-4 flex justify-end">
                        <button onclick="downloadImage()" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">
                            Download Image
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script>
        function openModal() {
            document.getElementById('testModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('testModal').classList.add('hidden');
        }

        function downloadImage() {
            const element = document.getElementById('invoice-content');
            const button = event.target;
            button.disabled = true;
            button.textContent = 'Generating...';
            
            html2canvas(element, {
                scale: 2,
                useCORS: true,
                logging: false,
                backgroundColor: '#ffffff'
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = 'Invoice-Test.png';
                link.href = canvas.toDataURL();
                link.click();
                
                button.disabled = false;
                button.textContent = 'Download Image';
            });
        }
    </script>
</body>
</html>
