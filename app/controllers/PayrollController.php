<?php
class PayrollController extends Controller
{
    public function index(): void
    {
        $this->authorize('payroll', 'view');
        $records = (new Payroll())->withStaff();
        $this->view('payroll/index', ['title' => 'Payroll', 'records' => $records]);
    }

    public function create(): void
    {
        $this->authorize('payroll', 'add');
        $staff = Database::fetchAll(
            "SELECT u.* FROM users u JOIN roles r ON r.id=u.role_id WHERE r.slug IN ('teacher','admin_staff') AND u.tuition_center_id=:c",
            ['c' => Auth::centerId()]
        );
        $this->view('payroll/create', ['title' => 'Add Payroll Entry', 'staff' => $staff]);
    }

    public function store(): void
    {
        $this->authorize('payroll', 'add');
        $this->validateCsrf();
        $data = Request::only(['staff_id','pay_period_month','pay_period_year','basic_salary','allowances','deductions','remarks']);
        $v = new Validator($data);
        $v->required('staff_id')->required('pay_period_month')->required('pay_period_year')->numeric('basic_salary');
        if ($v->fails()) redirect_with_error('/payroll/create', $v->firstError());

        $net = (float) ($data['basic_salary'] ?? 0) + (float) ($data['allowances'] ?? 0) - (float) ($data['deductions'] ?? 0);
        (new Payroll())->create($data + ['net_salary' => $net, 'status' => 'pending', 'created_by' => Auth::id()]);
        log_activity('create', 'payroll', "Created payroll entry for staff #{$data['staff_id']}");
        redirect_with_success('/payroll', 'Payroll entry created.');
    }

    public function edit(int $id): void
    {
        $this->authorize('payroll', 'edit');
        $record = (new Payroll())->find($id);
        if (!$record) Response::redirect('/payroll');
        $staff = Database::fetchAll(
            "SELECT u.* FROM users u JOIN roles r ON r.id=u.role_id WHERE r.slug IN ('teacher','admin_staff') AND u.tuition_center_id=:c",
            ['c' => Auth::centerId()]
        );
        $this->view('payroll/edit', ['title' => 'Edit Payroll', 'record' => $record, 'staff' => $staff]);
    }

    public function update(int $id): void
    {
        $this->authorize('payroll', 'edit');
        $this->validateCsrf();
        $data = Request::only(['staff_id','pay_period_month','pay_period_year','basic_salary','allowances','deductions','remarks']);
        $net = (float) ($data['basic_salary'] ?? 0) + (float) ($data['allowances'] ?? 0) - (float) ($data['deductions'] ?? 0);
        (new Payroll())->update($id, $data + ['net_salary' => $net]);
        redirect_with_success('/payroll', 'Payroll entry updated.');
    }

    public function destroy(int $id): void
    {
        $this->authorize('payroll', 'delete');
        $this->validateCsrf();
        (new Payroll())->delete($id);
        redirect_with_success('/payroll', 'Payroll entry deleted.');
    }

    public function markPaid(int $id): void
    {
        $this->authorize('payroll', 'edit');
        $this->validateCsrf();
        (new Payroll())->update($id, ['status' => 'paid', 'paid_at' => date('Y-m-d H:i:s')]);
        redirect_with_success('/payroll', 'Marked as paid.');
    }
}
