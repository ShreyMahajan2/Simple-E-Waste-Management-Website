<?php
include('includes/config.php');
session_start();
?>

<?php include('includes/header.php'); ?>

<div class="row">
    <div class="col-12">
        <div class="hero-section" style="border-radius: 15px; margin-bottom: 30px;">
            <h1 class="display-4 fw-bold">E-Waste Awareness</h1>
            <p class="lead">Understanding the importance of proper e-waste disposal</p>
        </div>
    </div>
</div>

<div class="row">
    <!-- What is E-Waste -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-info text-white">
                <h4>📱 What is E-Waste?</h4>
            </div>
            <div class="card-body">
                <p>Electronic waste (e-waste) refers to discarded electrical or electronic devices. Used electronics which are destined for refurbishment, reuse, resale, salvage recycling through material recovery, or disposal are also considered e-waste.</p>
                <ul>
                    <li>Computers and laptops</li>
                    <li>Mobile phones and tablets</li>
                    <li>Televisions and monitors</li>
                    <li>Printers and scanners</li>
                    <li>Household appliances</li>
                    <li>Batteries and chargers</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Why Recycle -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <h4>♻️ Why Recycle E-Waste?</h4>
            </div>
            <div class="card-body">
                <h5>Environmental Protection</h5>
                <p>Prevents toxic substances from contaminating soil and water:</p>
                <ul>
                    <li>Lead - damages nervous system</li>
                    <li>Mercury - harmful to brain</li>
                    <li>Cadmium - causes kidney damage</li>
                    <li>Brominated flame retardants</li>
                </ul>
                
                <h5>Resource Conservation</h5>
                <p>Recovers valuable materials:</p>
                <ul>
                    <li>Gold, silver, copper</li>
                    <li>Rare earth metals</li>
                    <li>Plastics and glass</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Recycling Process -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-warning text-dark">
                <h4>🔄 E-Waste Recycling Process</h4>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3 mb-3">
                        <div class="feature-box bg-light-green">
                            <h5>1. Collection</h5>
                            <p>E-waste is collected from drop-off points</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="feature-box bg-light">
                            <h5>2. Sorting</h5>
                            <p>Devices are sorted by type and condition</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="feature-box bg-light-green">
                            <h5>3. Dismantling</h5>
                            <p>Manual disassembly into components</p>
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <div class="feature-box bg-light">
                            <h5>4. Processing</h5>
                            <p>Materials are separated and recycled</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dos and Don'ts -->
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <h4>✅ Do's of E-Waste Management</h4>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">✓ Use authorized e-waste recyclers</li>
                    <li class="list-group-item">✓ Remove personal data before disposal</li>
                    <li class="list-group-item">✓ Separate batteries from devices</li>
                    <li class="list-group-item">✓ Donate working electronics</li>
                    <li class="list-group-item">✓ Check for take-back programs</li>
                    <li class="list-group-item">✓ Educate others about e-waste</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-danger text-white">
                <h4>❌ Don'ts of E-Waste Management</h4>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">✗ Don't throw in regular trash</li>
                    <li class="list-group-item">✗ Don't burn electronic devices</li>
                    <li class="list-group-item">✗ Don't dispose with household waste</li>
                    <li class="list-group-item">✗ Don't stockpile old electronics</li>
                    <li class="list-group-item">✗ Don't attempt unsafe dismantling</li>
                    <li class="list-group-item">✗ Don't ignore local regulations</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Call to Action -->
<div class="row">
    <div class="col-12">
        <div class="card bg-light">
            <div class="card-body text-center py-5">
                <h3>Ready to Make a Difference?</h3>
                <p class="lead">Start responsibly disposing of your e-waste today</p>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="user/submit_waste.php" class="btn btn-success btn-lg">Submit E-Waste</a>
                <?php else: ?>
                    <a href="/e-waste-management/index.php" class="btn btn-success btn-lg">Get Started</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include('includes/footer.php'); ?>