<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>CA Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />

  <style>
    body {
      background: #f6f7fb;
      font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI",
        sans-serif;
    }

    .page {
      padding: 20px;
    }

    .topbar {
      border-radius: 16px;
    }

    .kpi {
      border: 0;
      border-radius: 14px;
    }

    .kpi .card-body {
      padding: 14px;
    }

    .label {
      font-size: 0.75rem;
      letter-spacing: 0.06em;
      text-transform: uppercase;
      color: #6b7280;
    }

    .value {
      font-size: 1.35rem;
      font-weight: 800;
    }

    .small-help {
      font-size: 0.85rem;
      color: #6b7280;
    }

    .table thead th {
      white-space: nowrap;
    }

    .navbtns a {
      margin-right: 8px;
      margin-bottom: 6px;
    }

    .chip {
      display: inline-block;
      border-radius: 999px;
      padding: 0.15rem 0.6rem;
      font-size: 0.75rem;
      border: 1px solid #d1d5db;
      background: #fff;
    }

    .link-muted {
      color: #374151;
      text-decoration: none;
    }

    .link-muted:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body>
  <section class="page">
    <div class="container-fluid">
      <div class="card shadow-sm topbar mb-3">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-2">
          <div>
            <h5 class="mb-0">CA View (Read-only)</h5>
            <div class="small-help">
              Download statements with purpose comments + attached
              bills/invoices. Limited edit only for task status.
            </div>
          </div>
          <div class="navbtns">
            <a class="btn btn-sm btn-primary" href="{{route('ca.dashboard')}}">CA Dashboard</a><a
              class="btn btn-sm btn-outline-primary" href="{{route('ca.statements')}}">Statements</a><a class="btn btn-sm btn-outline-primary"
              href="{{route('ca.invoices')}}">Invoices Repository</a><a class="btn btn-sm btn-outline-primary"
              href="ca_records.html">Records (Exp/Inc)</a><a class="btn btn-sm btn-outline-primary"
              href="ca_salary_packs.html">Salary Packs</a><a class="btn btn-sm btn-outline-primary"
              href="ca_tasks.html">Tasks & Reminders</a>
          </div>
        </div>
      </div>
      @yield('content')
    </div>
  </section>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
      @if ($errors->any())
          let errorMessages = '';
          @foreach ($errors->all() as $error)
              errorMessages += '<li>{{ $error }}</li>';
          @endforeach
          Swal.fire({
              icon: 'error',
              title: 'Validation Error',
              html: '<ul style="text-align: left; margin-bottom: 0;">' + errorMessages + '</ul>',
              confirmButtonColor: '#3b82f6',
          });
      @endif

      @if(session('success'))
          Swal.fire({
              icon: 'success',
              title: 'Success',
              text: '{{ session('success') }}',
              timer: 3000,
              showConfirmButton: false
          });
      @endif
      
      @if(session('error'))
          Swal.fire({
              icon: 'error',
              title: 'Error',
              text: '{{ session('error') }}',
              confirmButtonColor: '#3b82f6',
          });
      @endif
  </script>
</body>

</html>