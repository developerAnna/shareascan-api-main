@if (isset($qrcode))
    <div
        style="max-width: 400px; margin: 20px auto; border: 1px solid #e0e0e0; border-radius: 10px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); overflow: hidden; background-color: #fff;">
        <div style="padding: 20px; text-align: center; background-color: #f8f9fa;">
            <h3 style="font-size: 1.5rem; margin-bottom: 10px; color: #09122C;">QR Code Content</h3>
            <p style="font-size: 1.25rem; font-weight: 600; color: #333;">
                {{ $qrcode->qr_content }}
            </p>
        </div>
    </div>
@endif

@if (isset($fileUrl))
    <div class="document-container">
        <iframe src="{{ $fileUrl }}" width="100%" height="600px" style="border: none;"></iframe>
    </div>
@else
    <p>No document available.</p>
@endif
