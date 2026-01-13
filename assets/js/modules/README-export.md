# Export CSV Feature

## 📋 Overview

Xuất dữ liệu giao dịch ra file CSV với đầy đủ thông tin và tổng kết.

---

## ✨ Features

### 1. **Smart Export**

- Xuất tất cả giao dịch nếu không có filter
- Xuất chỉ giao dịch đã lọc nếu có filter active
- Tự động include các filter parameters

### 2. **Summary Section**

File CSV bao gồm:

- **Tổng Thu**: Tổng tiền thu nhập
- **Tổng Chi**: Tổng tiền chi tiêu
- **Số Dư**: Thu - Chi
- **Tổng Giao Dịch**: Số lượng records
- **Xuất Lúc**: Timestamp

### 3. **Excel Compatible**

- UTF-8 BOM encoding → Mở được trực tiếp trong Excel
- Format số tiền chuẩn VN: `1.000.000`
- Format ngày: `26/12/2024`

### 4. **Auto Filename**

Format: `giao-dich-YYYY-MM-DD_HHmmss.csv`  
Ví dụ: `giao-dich-2024-12-26_153045.csv`

---

## 🏗️ Architecture

```
User clicks Export
    ↓
ExportHandler.exportToCSV()
    ↓
Get current filter params
    ↓
Call api/transactions/export.php?params
    ↓
PHP generates CSV with summary
    ↓
Browser downloads file
    ↓
Show success notification
```

---

## 📝 CSV Format

```csv
=== TỔNG KẾT ===
Tổng Thu,15.000.000 Đ
Tổng Chi,5.000.000 Đ
Số Dư,10.000.000 Đ
Tổng Giao Dịch,150
Xuất Lúc,26/12/2024 15:30:45

STT,Ngày,Loại,Danh Mục,Số Tiền (VNĐ),Mô Tả
1,26/12/2024,Thu nhập,Lương,15.000.000,Nhận lương tháng 12
2,25/12/2024,Chi tiêu,Ăn uống,50.000,Ăn sáng
...
```

---

## 🎯 Use Cases

### Case 1: Export tất cả

```
1. Không apply filter nào
2. Bấm "📥 Xuất CSV"
→ Export toàn bộ database
```

### Case 2: Export theo tháng

```
1. Bấm "Tháng này"
2. Bấm "📥 Xuất CSV"
→ Export chỉ giao dịch tháng này
```

### Case 3: Export chi tiêu ăn uống

```
1. Chọn Type: Chi tiêu
2. Chọn Category: Ăn uống
3. Bấm "📥 Xuất CSV"
→ Export chỉ chi tiêu ăn uống
```

### Case 4: Export search results

```
1. Gõ "lương" vào search
2. Đợi filter (500ms)
3. Bấm "📥 Xuất CSV"
→ Export chỉ giao dịch có "lương"
```

---

## 🔧 Technical Details

### Backend: `api/transactions/export.php`

**Inputs** (GET parameters):

- `type` - Filter by income/expense
- `category_id` - Filter by category
- `search` - Search in description
- `date_from` - Start date
- `date_to` - End date

**Output**: CSV file with headers:

- `Content-Type: text/csv; charset=utf-8`
- `Content-Disposition: attachment; filename="..."`
- UTF-8 BOM prefix for Excel

**SQL**: Same logic as `filter.php`

### Frontend: `export-handler.js`

**Method**: `exportToCSV()`

- Get current filter params
- Build export URL
- Create hidden iframe for download
- Show loading state
- Display success notification

**Dependencies**:

- `Utils.showNotification()`

---

## 🎨 UI Components

### Button

```html
<button id="btnExport" type="button" class="btn-export">📥 Xuất CSV</button>
```

### Styles

```css
.btn-export {
  background-color: #3498db; /* Blue */
  min-width: 120px;
}
```

---

## 🧪 Testing Checklist

- [ ] Export all transactions (no filter)
- [ ] Export with type filter
- [ ] Export with category filter
- [ ] Export with date range
- [ ] Export with search query
- [ ] Export combined filters
- [ ] Open CSV in Excel → Check encoding
- [ ] Check summary totals are correct
- [ ] Check data format (date, money)
- [ ] Mobile: Button width responsive

---

## 💡 Future Enhancements

### Ideas for later:

1. **Export to Excel (.xlsx)** - Richer format
2. **Export to PDF** - Professional reports
3. **Email export** - Send to email
4. **Scheduled exports** - Daily/weekly/monthly
5. **Export templates** - Custom column selection
6. **Chart exports** - Include visualizations

---

## 🐛 Troubleshooting

### Issue: File không tải về

**Solution**: Check console logs, verify export.php exists

### Issue: Excel hiển thị lỗi font

**Solution**: UTF-8 BOM đã được thêm, update Excel

### Issue: Số tiền bị lỗi format

**Solution**: Check `number_format()` in export.php

### Issue: Empty file

**Solution**: Check filter params, verify database has data

---

## 📚 Related Files

- `api/transactions/export.php` - Backend API
- `assets/js/modules/export-handler.js` - Frontend logic
- `assets/css/modules/filter.css` - Button styles
- `includes/footer.php` - Script inclusion
- `index.php` - Button UI

---

## 👨‍💻 Interview Talking Points

> "Em implement export CSV với:
>
> - **Smart filtering**: Tự động áp dụng filter hiện tại
> - **Excel compatible**: UTF-8 BOM encoding
> - **User-friendly**: Loading states, notifications
> - **Summary section**: Tổng kết trước data
> - **Clean code**: Modular architecture
>
> Backend tái sử dụng logic filter, Frontend dùng hidden iframe để download. File name có timestamp để tránh conflict."

---

Perfect for showing employers you understand:

- File generation & downloads
- Data export best practices
- Excel compatibility
- Modular code structure
- User experience design
