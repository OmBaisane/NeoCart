<?php
$base = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ - NeoCart</title>
    <link rel="stylesheet" href="<?php echo $base; ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $base; ?>assets/font-awesome/css/all.min.css">
    <link rel="stylesheet" href="<?php echo $base; ?>assets/css/animate.min.css">
    <link rel="stylesheet" href="<?php echo $base; ?>assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="text-center mb-5 animate__animated animate__fadeIn">Frequently Asked Questions</h1>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <!-- Question 1 -->
                    <div class="accordion-item animate__animated animate__fadeInUp">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                <i class="fas fa-shopping-cart me-2"></i> How do I place an order?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                To place an order, simply browse our products, add items to your cart, and proceed to checkout. You'll need to create an account or login to complete your purchase.
                            </div>
                        </div>
                    </div>

                    <!-- Question 2 -->
                    <div class="accordion-item animate__animated animate__fadeInUp">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                <i class="fas fa-credit-card me-2"></i> What payment methods do you accept?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We accept all major credit/debit cards, net banking, UPI, and popular digital wallets like Paytm, PhonePe, and Google Pay.
                            </div>
                        </div>
                    </div>

                    <!-- Question 3 -->
                    <div class="accordion-item animate__animated animate__fadeInUp">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                <i class="fas fa-truck me-2"></i> What is your delivery time?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Standard delivery takes 3-5 business days. Express delivery is available for metro cities with 1-2 day delivery. Delivery times may vary based on your location.
                            </div>
                        </div>
                    </div>

                    <!-- Question 4 -->
                    <div class="accordion-item animate__animated animate__fadeInUp">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                <i class="fas fa-undo me-2"></i> What is your return policy?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We offer a 30-day return policy for most items. Products must be in original condition with tags attached. Some items like electronics may have different return policies.
                            </div>
                        </div>
                    </div>

                    <!-- Question 5 -->
                    <div class="accordion-item animate__animated animate__fadeInUp">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                <i class="fas fa-lock me-2"></i> Is my personal information secure?
                            </button>
                        </h2>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes, we use SSL encryption to protect your personal information. We never share your data with third parties without your consent.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-5">
                    <h5>Still have questions?</h5>
                    <p>Contact our customer support team for assistance.</p>
                    <a href="contact.php" class="btn btn-primary">
                        <i class="fas fa-headset me-2"></i>Contact Support
                    </a>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>