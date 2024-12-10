<!-- resources/views/donate.blade.php -->

@extends('layouts.app')

@section('content')
<!-- <div class="donate-container mt-3">
    <div class="donate-header">
        <h1>Support Our Mission</h1>
        <p>Your contributions make a difference. Help us continue providing valuable content.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('donate') }}" method="POST">
        @csrf
        <div class="donation-options">
            <div class="donation-card">
                <h2>$10</h2>
                <p>Helps cover basic site maintenance costs.</p>
                <button type="submit" name="amount" value="10" class="donate-btn">Donate $10</button>
            </div>
            <div class="donation-card">
                <h2>$25</h2>
                <p>Supports content creation and improvement.</p>
                <button type="submit" name="amount" value="25" class="donate-btn">Donate $25</button>
            </div>
            <div class="donation-card">
                <h2>$50</h2>
                <p>Contributes to major upgrades and new features.</p>
                <button type="submit" name="amount" value="50" class="donate-btn">Donate $50</button>
            </div>
            <div class="donation-card">
                <h2>Custom</h2>
                <input type="number" min="1" placeholder="Enter amount" name="amount" class="custom-amount">
                <button type="submit" class="donate-btn">Donate Custom</button>
            </div>
        </div>
    </form>

    <div class="donate-footer">
        <p>Thank you for supporting our mission!</p>
        <p>All donations are secure and greatly appreciated.</p>
    </div>
</div> -->
<style>

/* Donate Page Styles */
.donate-container {
    max-width: 800px;
    margin: auto;
    padding: 20px;
    background: #ffffff;
    box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    text-align: center;
}

.donate-header h1 {
    font-size: 2em;
    color: #333;
    margin-bottom: 0.5em;
}

.donate-header p {
    font-size: 1.1em;
    color: #777;
    margin-bottom: 1.5em;
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 5px;
}

.donation-options {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    justify-content: center;
    margin-bottom: 1.5em;
}

.donation-card {
    flex: 1 1 200px;
    background: #f9f9f9;
    border: 1px solid #eeeeee;
    padding: 20px;
    border-radius: 10px;
    text-align: center;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
}

.donation-card h2 {
    font-size: 1.5em;
    color: #333;
    margin-bottom: 0.5em;
}

.donation-card p {
    font-size: 1em;
    color: #555;
    margin-bottom: 1em;
}

.custom-amount {
    width: 100%;
    padding: 8px;
    margin-bottom: 1em;
    border-radius: 5px;
    border: 1px solid #cccccc;
}

.donate-btn {
    padding: 10px 20px;
    color: #ffffff;
    background-color: #007bff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    font-size: 1em;
    transition: background-color 0.3s ease;
}

.donate-btn:hover {
    background-color: #0056b3;
}

.donate-footer {
    font-size: 0.9em;
    color: #777;
    margin-top: 1.5em;
}


</style>
@endsection

