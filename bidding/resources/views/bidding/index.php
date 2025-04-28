<div class="row mb-4">
    <div class="col-md-8">
        <h1>Licitações</h1>
    </div>
    <div class="col-md-4 text-end">
        <a href="<?= route('biddings.create') ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> Nova Licitação
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <form action="<?= route('biddings.index') ?>" method="GET" class="row g-3">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Buscar por título ou número do edital" value="<?= request()->get('search') ?>">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Todos os Status</option>
                    <?php foreach ($statuses ?? [] as $status): ?>
                    <option value="<?= $status->id ?>" <?= request()->get('status') == $status->id ? 'selected' : '' ?>>
                        <?= htmlspecialchars($status->name) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="date" class="form-control" value="<?= request()->get('date') ?>">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">Filtrar</button>
            </div>
        </form>
    </div>

    <div class="card-body">
        <?php if (count($biddings) > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Título</th>
                        <th>Número do Edital</th>
                        <th>Entidade</th>
                        <th>Data de Abertura</th>
                        <th>Data de Encerramento</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($biddings as $bidding): ?>
                    <tr>
                        <td><?= htmlspecialchars($bidding->title) ?></td>
                        <td><?= htmlspecialchars($bidding->notice_number) ?></td>
                        <td><?= htmlspecialchars($bidding->entity) ?></td>
                        <td><?= $bidding->opening_date->format('d/m/Y H:i') ?></td>
                        <td><?= $bidding->closing_date->format('d/m/Y H:i') ?></td>
                        <td>
                            <span class="badge bg-<?= getBadgeClass($bidding->status->name) ?>">
                                <?= htmlspecialchars($bidding->status->name) ?>
                            </span>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="<?= route('biddings.show', $bidding->id) ?>" class="btn btn-info" title="Visualizar">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="<?= route('biddings.edit', $bidding->id) ?>" class="btn btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger delete-btn" data-id="<?= $bidding->id ?>" title="Excluir">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            <?= $biddings->links() ?>
        </div>

        <?php else: ?>
        <div class="alert alert-info mb-0">
            Nenhuma licitação encontrada.
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Exclusão</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir esta licitação? Esta ação não pode ser desfeita.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="_method" value="DELETE">
                    <button type="submit" class="btn btn-danger">Excluir</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurar o modal de exclusão
    const deleteButtons = document.querySelectorAll('.delete-btn');
    const deleteForm = document.getElementById('deleteForm');

    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            deleteForm.action = `/biddings/${id}`;

            // Abrir o modal
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        });
    });
});

// Função auxiliar para determinar a classe do badge baseado no status
function getBadgeClass(status) {
    const classes = {
        'Publicada': 'primary',
        'Em andamento': 'info',
        'Encerrada': 'secondary',
        'Cancelada': 'danger',
        'Concluída': 'success',
        'Impugnada': 'warning'
    };

    return classes[status] || 'secondary';
}
</script>
