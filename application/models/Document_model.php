<?php
defined('BASEPATH') or exit('No direct script access allowed');
class Document_model extends MY_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->_table = 'documents';
        $this->delete_db = false;
        $this->delete_tbref = array();
    }

    public function data()
    {
        $data = array(
            'id' => null,
            'doc_no' => null,
            'doc_date' => null,
            'due_date' => null,
            'ref_doc' => null,
            'payment_type' => null,
            'credit_day' => null,
            'contact_name' => null,
            'contact_address' => null,
            'contact_email' => null,
            'contact_tel' => null,
            'contact_fax' => null,
            'contact_tax_no' => null,
            'contact_branch_name' => null,
            'remark' => null,
            'vat_type' => null,
            'vat' => 0,
            'discount' => 0,
            'total' => 0,
            'grand_total' => 0,
            'type' => null,            
            'updated_at' => null,
            'updated_by' => null,
            'balance' => 0,
            'pay_total' => 0,
            'status' => null,
            'created_at' => null,
            'created_by' => null,
            'contact_id' => null,
        );
        return $data;
    }

    public function get_with_page($param)
    {
        $keyword = $param['keyword'];
        $this->db->select('d.*, c.name');

        $condition = "d.doc_date between '{$param['start_doc_date']}' and '{$param['end_doc_date']}'";
        $condition .= !empty($keyword) ? " and (d.doc_no like '%{$keyword}%')" : "";        
        $condition .= !empty($param['status']) ? " and d.status='{$param['status']}'" : "";        

        $this->db->from('documents d');
        $this->db->join('contacts c', 'd.contact_id=c.id', 'inner');
        $this->db->where($condition);
        $this->db->limit($param['page_size'], $param['start']);
        $this->db->order_by($param['column'], $param['dir']);

        $query = $this->db->get();
        $data = ($query->num_rows() > 0) ? $query->result_array() : [];

        $count_condition = $this->db->from('documents d')
            ->join('contacts c', 'd.contact_id=c.id', 'inner')
            ->where($condition)
            ->count_all_results();
        $count = $this->db->from($this->_table)->count_all_results();
        $result = array('count' => $count, 'count_condition' => $count_condition, 'data' => $data, 'error_message' => '');
        return $result;
    }

    public function get_by_id($id)
    {
        $query = $this->db->select('h.*, d.id as detail_id, d.product_name, d.quantity, d.price, d.product_id')
            ->from('documents h')
            ->join('document_details d', 'h.id=d.document_id', 'inner')
            ->where('h.id', $id)
            ->get();
        return $query->row_array();
    }

    public function employee_leave($param)
    {
        $query = $this->db->select('ifnull(sum(l.total), 0) as total')
            ->from('leaves l')
            ->join('employees e', 'l.employee_id=e.id', 'inner')
            ->where('e.id', $param['employee_id'])
            ->where('l.status', $param['status'])
            ->where_in('leave_type_id', array(1, 3, 10))
            ->get();
        return $query->row_array();
    }

    public function get_all_with_param($param = array())
    {

        $this->db->select('l.*, e.firstname, e.lastname, d.name as department_name, t.name as leave_type_name, e.profile_picture')
            ->from('leaves l')
            ->join('employees e', 'l.employee_id=e.id', 'inner')
            ->join('departments d', 'e.department_id=d.id', 'inner')
            ->join('leave_types t', 't.id=l.leave_type_id', 'inner')
            ->limit(10, 0)
            ->order_by('l.created_at', 'desc');

        if (!empty($param['employee_id'])) {
            $this->db->where('l.employee_id', $param['employee_id']);
        }

        $query = $this->db->get();
        return $query->result_array();
    }

    public function get_summary($param = array())
    {
        $condition = !empty($param['status']) ? "l.status='{$param['status']}'" : "l.status='2'";

        $this->db->select('e.id, e.firstname, e.lastname, l.start_date, l.end_date, l.total, month(l.start_date) as start_month, month(l.end_date) as end_month')
            ->from('leaves l')
            ->join('employees e', 'l.employee_id=e.id', 'inner')
            ->where($condition)
            ->order_by('e.firstname', 'asc');

        $query = $this->db->get();
        return $query->result_array();
    }
}