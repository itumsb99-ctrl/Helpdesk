
<div class="border-top mt-4 pt-3">
        <h6 class="fw-bold mb-3">
            <i class="bi bi-paperclip"></i>
            Lampiran Bukti Gangguan
        </h6>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form
            action="{{ route('tickets.attachments.store', $ticket) }}"
            method="POST"
            enctype="multipart/form-data"
        >
            @csrf

            <div class="mb-3">
                <label class="form-label fw-bold">
                    Upload Foto atau Dokumen
                </label>

                <input
                    type="file"
                    name="attachments[]"
                    class="form-control"
                    multiple
                    accept=".jpg,.jpeg,.png,.pdf,.docx"
                    required
                >

                <small class="text-muted">
                    Format JPG, JPEG, PNG, PDF, DOCX.
                    Maksimal 5 MB per file.
                </small>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-upload"></i>
                Upload Lampiran
            </button>
        </form>

        <hr>

        <h6 class="fw-bold mb-3">Daftar Lampiran</h6>

        @forelse($ticket->attachments as $attachment)

            <div class="d-flex justify-content-between
                        align-items-center border-bottom py-3">

                <div class="d-flex align-items-center gap-2">

                    <i class="bi bi-file-earmark"
                       style="font-size:25px"></i>

                    <div>
                        <div class="fw-semibold">
                            {{ $attachment->original_name }}
                        </div>

                        <small class="text-muted">
                            {{ number_format($attachment->file_size / 1024, 0) }}
                            KB |
                            {{ $attachment->created_at->format('d/m/Y H:i') }}
                        </small>
                    </div>
                </div>

                <div class="d-flex gap-2">

                    <a
                        href="{{ route('attachments.download', $attachment) }}"
                        class="btn btn-sm btn-success"
                    >
                        <i class="bi bi-download"></i>
                        Download
                    </a>

                    @if(auth()->user()->role === 'admin'
                        || (int) $attachment->uploaded_by === (int) auth()->id())

                        <form
                            action="{{ route('attachments.destroy', $attachment) }}"
                            method="POST"
                            onsubmit="return confirm('Yakin ingin menghapus lampiran?')"
                        >
                            @csrf
                            @method('DELETE')

                            <button class="btn btn-sm btn-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>

                    @endif

                </div>
            </div>

        @empty

            <div class="text-center text-muted py-3">
                <i class="bi bi-folder2-open"
                   style="font-size:35px"></i>

                <p class="mb-0">
                    Belum ada lampiran bukti gangguan.
                </p>
            </div>

        @endforelse

</div>
