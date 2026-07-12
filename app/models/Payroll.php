<?php
class Payroll extends Model
{
    protected string $table = 'payroll';
    protected array $fillable = [
        'tuition_center_id','staff_id','pay_period_month','pay_period_year','basic_salary',
        'allowances','deductions','net_salary','status','paid_at','remarks','created_by'
    ];

    public function withStaff(): array
    {
        return Database::fetchAll(
            "SELECT p.*, u.first_name, u.last_name FROM payroll p JOIN users u ON u.id = p.staff_id
             WHERE p.tuition_center_id = :c ORDER BY p.pay_period_year DESC, p.pay_period_month DESC",
            ['c' => Auth::centerId()]
        );
    }
}
