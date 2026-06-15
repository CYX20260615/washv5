<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>上门洗鞋 · 在线下单</title>
    <style>
        * { box-sizing: border-box; font-family: system-ui, sans-serif; }
        body { background: #f0f2f5; padding: 20px; margin: 0; }
        .container { max-width: 600px; margin: 0 auto; }
        .card { background: white; border-radius: 32px; padding: 24px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        h2 { margin-top: 0; }
        .coupon-badge { background: #fefce8; border-left: 4px solid #eab308; padding: 12px; border-radius: 20px; margin-bottom: 20px; font-size: 0.9rem; }
        .form-group { margin-bottom: 16px; }
        label { display: block; font-weight: 500; margin-bottom: 6px; }
        input, select, textarea { width: 100%; padding: 12px; border: 1px solid #cfdee9; border-radius: 28px; font-size: 1rem; }
        button { width: 100%; padding: 14px; border-radius: 40px; background: #3b82f6; color: white; font-weight: bold; border: none; cursor: pointer; font-size: 1rem; }
        .shoes-row { display: flex; gap: 8px; margin-bottom: 12px; align-items: center; flex-wrap: wrap; background: #f8fafc; padding: 12px; border-radius: 28px; }
        .shoes-row select { flex: 2; margin: 0; }
        .shoes-row input.qty { width: 80px; margin: 0 8px; }
        .shoes-row .line-total { min-width: 70px; text-align: right; font-size: 0.9rem; }
        .shoes-row .remove-btn { width: auto; background: #ef4444; padding: 6px 12px; }
        .total-price { background: #e6f7e6; padding: 12px; border-radius: 28px; margin: 16px 0; text-align: right; font-weight: bold; }
        .discount { color: #16a34a; }
        .remark-options { display: flex; gap: 12px; flex-wrap: wrap; margin-bottom: 12px; }
        .remark-chip { background: #e2e8f0; padding: 6px 12px; border-radius: 40px; font-size: 0.85rem; cursor: pointer; }
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); align-items: center; justify-content: center; z-index: 1000; }
        .modal-content { background: white; max-width: 500px; width: 90%; border-radius: 32px; padding: 24px; max-height: 80%; overflow-y: auto; }
        .query-area { margin-top: 20px; border-top: 1px solid #cfdee9; padding-top: 20px; }
        .order-history-item { background: #f8fafc; border-radius: 20px; padding: 12px; margin-bottom: 12px; border-left: 4px solid #3b82f6; }
        .status-badge { display: inline-block; padding: 2px 8px; border-radius: 20px; font-size: 0.7rem; font-weight: bold; }
        .status-pending { background: #fef3c7; color: #b45309; }
        .status-processing { background: #dbeafe; color: #1e40af; }
        .status-completed { background: #d1fae5; color: #065f46; }
        .status-after_sale { background: #f3e8ff; color: #6b21a5; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>
<body>
<div class="container">
    <div class="card">
        <h2>🧼 上门洗鞋 · 在线下单</h2>
        <div id="couponDisplay" class="coupon-badge">加载优惠规则...</div>

        <div class="form-group">
            <label>姓名</label>
            <input type="text" id="name" placeholder="您的姓名">
        </div>
        <div class="form-group">
            <label>电话</label>
            <input type="tel" id="phone" placeholder="手机号码">
        </div>
        <div class="form-group">
            <label>地址</label>
            <input type="text" id="address" placeholder="详细地址">
        </div>
        <div class="form-group">
            <label>预约日期</label>
            <input type="date" id="date">
        </div>
        <div class="form-group">
            <label>预约时段</label>
            <select id="time">
                <option>上午 9:00-12:00</option>
                <option>下午 14:00-18:00</option>
                <option>晚上 18:00-21:00</option>
            </select>
        </div>

        <div style="margin: 16px 0 8px;">
            <label>鞋子明细</label>
            <button type="button" id="addShoeBtn" style="width: auto; padding: 6px 16px; margin-bottom: 10px;">+ 添加鞋类</button>
        </div>
        <div id="shoesContainer"></div>
        <div id="totalDisplay" class="total-price">💰 预估总价：0.00 元</div>

        <div class="form-group">
            <label>备注</label>
            <div class="remark-options">
                <span class="remark-chip" data-remark="不要电话打扰">🔇 不要电话打扰</span>
                <span class="remark-chip" data-remark="放门口">🚪 放门口</span>
                <span class="remark-chip" data-remark="请先电话联系">📞 请先电话联系</span>
                <span class="remark-chip" data-remark="加急处理">⚡ 加急处理</span>
            </div>
            <textarea id="remark" rows="2" placeholder="其他要求..."></textarea>
            <div class="form-group" style="margin-top: 8px;">
                <label>加急时间要求 (如需要)</label>
                <input type="text" id="urgent" placeholder="例如：今天下午6点前">
            </div>
        </div>

        <button id="previewBtn">📦 确认订单</button>
        <div id="msg"></div>

        <div class="query-area">
            <button id="showOrdersBtn" style="background:#10b981;">📋 查询我的订单</button>
            <div id="orderHistory" style="display:none; margin-top: 16px;">
                <input type="tel" id="queryPhone" placeholder="输入下单手机号" style="margin-bottom: 12px;">
                <button id="doQueryBtn" style="background:#3b82f6;">查询</button>
                <div id="orderHistoryList" style="margin-top: 16px;"></div>
            </div>
        </div>
    </div>
</div>

<div id="orderModal" class="modal">
    <div class="modal-content">
        <h3>📋 请确认订单信息</h3>
        <div id="modalContent"></div>
        <button id="confirmOrderBtn" style="background:#10b981;">✅ 确认提交</button>
        <button id="closeModalBtn" style="background:#6c7a91; margin-top:8px;">✖️ 返回修改</button>
    </div>
</div>

<script>
    const apiUrl = 'api.php';
    let config = null;
    let activeShoes = [];

    // 记忆功能
    function loadSavedCustomer() {
        const saved = localStorage.getItem('washCustomer');
        if (saved) {
            try {
                const data = JSON.parse(saved);
                if (data.name) document.getElementById('name').value = data.name;
                if (data.phone) document.getElementById('phone').value = data.phone;
                if (data.address) document.getElementById('address').value = data.address;
            } catch(e) {}
        }
    }

    function saveCustomerInfo() {
        const name = document.getElementById('name').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const address = document.getElementById('address').value.trim();
        if (name && phone && address) {
            localStorage.setItem('washCustomer', JSON.stringify({ name, phone, address }));
        }
    }

    async function loadConfig() {
        const res = await axios.get(`${apiUrl}?action=getConfig`);
        config = res.data;
        activeShoes = (config.shoes || []).filter(s => s.enabled === true).map(s => ({ name: s.name, price: s.price }));
        const coupon = config.coupon;
        let promoHtml = '';
        if (coupon.enabled && coupon.full_amount > 0 && coupon.reduce_amount > 0) {
            promoHtml = `🎁 当前活动：满 <strong>${coupon.full_amount}</strong> 元减 <strong>${coupon.reduce_amount}</strong> 元（自动优惠）`;
        } else {
            promoHtml = '📌 暂无优惠活动';
        }
        if (config.custom_promo) promoHtml += `<br>✨ ${config.custom_promo}`;
        document.getElementById('couponDisplay').innerHTML = promoHtml;
        initShoes();
        loadSavedCustomer();
    }

    function initShoes() {
        document.getElementById('shoesContainer').innerHTML = '';
        if (activeShoes.length) addShoeRow(0, 1);
        else document.getElementById('shoesContainer').innerHTML = '<div style="color:red;">暂无可用鞋类，请联系商家</div>';
    }

    function addShoeRow(selectedIdx = 0, qty = 1) {
        if (!activeShoes.length) return;
        const container = document.getElementById('shoesContainer');
        const rowDiv = document.createElement('div');
        rowDiv.className = 'shoes-row';

        const select = document.createElement('select');
        activeShoes.forEach((shoe, idx) => {
            const opt = document.createElement('option');
            opt.value = idx;
            opt.textContent = `${shoe.name} (${shoe.price}元/双)`;
            if (idx === selectedIdx) opt.selected = true;
            select.appendChild(opt);
        });
        select.addEventListener('change', () => updateRowTotal(rowDiv));

        const qtyInput = document.createElement('input');
        qtyInput.type = 'number';
        qtyInput.step = '1';
        qtyInput.value = qty;
        qtyInput.min = '1';
        qtyInput.className = 'qty';
        qtyInput.style.width = '80px';
        qtyInput.addEventListener('input', () => updateRowTotal(rowDiv));

        const lineTotalSpan = document.createElement('span');
        lineTotalSpan.className = 'line-total';
        lineTotalSpan.textContent = '0.00 元';

        const delBtn = document.createElement('button');
        delBtn.textContent = '删除';
        delBtn.className = 'remove-btn';
        delBtn.addEventListener('click', () => {
            rowDiv.remove();
            refreshTotal();
        });

        rowDiv.appendChild(select);
        rowDiv.appendChild(qtyInput);
        rowDiv.appendChild(lineTotalSpan);
        rowDiv.appendChild(delBtn);
        container.appendChild(rowDiv);
        updateRowTotal(rowDiv);
        refreshTotal();
    }

    function updateRowTotal(rowDiv) {
        const select = rowDiv.querySelector('select');
        const idx = parseInt(select.value);
        const price = activeShoes[idx].price;
        const qty = parseFloat(rowDiv.querySelector('.qty').value) || 0;
        const lineTotal = price * qty;
        rowDiv.querySelector('.line-total').textContent = `${lineTotal.toFixed(2)} 元`;
        refreshTotal();
    }

    function getCurrentSubtotal() {
        let subtotal = 0;
        document.querySelectorAll('#shoesContainer .shoes-row').forEach(row => {
            const select = row.querySelector('select');
            const idx = parseInt(select.value);
            const price = activeShoes[idx].price;
            const qty = parseFloat(row.querySelector('.qty').value) || 0;
            subtotal += price * qty;
        });
        return subtotal;
    }

    function refreshTotal() {
        let subtotal = getCurrentSubtotal();
        let total = subtotal;
        let discount = 0;
        const coupon = config.coupon;
        if (coupon.enabled && subtotal >= coupon.full_amount) {
            total = subtotal - coupon.reduce_amount;
            if (total < 0) total = 0;
            discount = coupon.reduce_amount;
        }
        let totalHtml = `<div>💰 小计：${subtotal.toFixed(2)} 元</div>`;
        if (discount > 0) totalHtml += `<div class="discount">🎉 已享优惠：-${discount} 元</div>`;
        totalHtml += `<div><strong>💰 应付总价：${total.toFixed(2)} 元</strong></div>`;
        if (coupon.enabled && subtotal < coupon.full_amount && coupon.full_amount - subtotal <= 10) {
            const need = coupon.full_amount - subtotal;
            totalHtml += `<div style="color:#eab308;">💡 再加 ${need} 元即可享受满减优惠哦~</div>`;
        }
        document.getElementById('totalDisplay').innerHTML = totalHtml;
        return { subtotal, total, discount };
    }

    function getShoesDetailWithSubtotal() {
        let detail = '';
        document.querySelectorAll('#shoesContainer .shoes-row').forEach(row => {
            const select = row.querySelector('select');
            const idx = parseInt(select.value);
            const shoeName = activeShoes[idx].name;
            const price = activeShoes[idx].price;
            const qty = parseFloat(row.querySelector('.qty').value) || 0;
            const lineTotal = price * qty;
            detail += `${shoeName} ×${qty} (${price}元/双) = ${lineTotal}元； `;
        });
        return detail || '无';
    }

    function buildOrderPreview() {
        const name = document.getElementById('name').value.trim();
        const phone = document.getElementById('phone').value.trim();
        const address = document.getElementById('address').value.trim();
        if (!name || !phone || !address) {
            alert('请填写姓名、电话和地址');
            return null;
        }
        const date = document.getElementById('date').value || '未指定';
        const time = document.getElementById('time').value;
        const shoesDetail = getShoesDetailWithSubtotal();
        const subtotal = getCurrentSubtotal();
        const { total, discount } = refreshTotal();
        let remark = document.getElementById('remark').value.trim();
        const urgent = document.getElementById('urgent').value.trim();
        if (urgent) remark += (remark ? '；' : '') + `加急：${urgent}`;
        return { name, phone, address, date, time, shoesDetail, subtotal, total, discount, remark };
    }

    function showModal() {
        const preview = buildOrderPreview();
        if (!preview) return;
        const modalContent = document.getElementById('modalContent');
        modalContent.innerHTML = `
            <p><strong>姓名：</strong> ${escapeHtml(preview.name)}</p>
            <p><strong>电话：</strong> ${escapeHtml(preview.phone)}</p>
            <p><strong>地址：</strong> ${escapeHtml(preview.address)}</p>
            <p><strong>预约：</strong> ${escapeHtml(preview.date)} ${escapeHtml(preview.time)}</p>
            <p><strong>鞋子明细：</strong> ${escapeHtml(preview.shoesDetail)}</p>
            <p><strong>小计：</strong> ${preview.subtotal.toFixed(2)} 元</p>
            ${preview.discount > 0 ? `<p><strong>优惠减免：</strong> -${preview.discount} 元</p>` : ''}
            <p><strong style="font-size:1.2rem;">应付总价：${preview.total.toFixed(2)} 元</strong></p>
            <p><strong>备注：</strong> ${escapeHtml(preview.remark)}</p>
        `;
        document.getElementById('orderModal').style.display = 'flex';
        window.currentOrderPreview = preview;
    }

    async function submitOrder() {
        const order = window.currentOrderPreview;
        if (!order) return;
        const orderData = {
            name: order.name,
            phone: order.phone,
            address: order.address,
            date: order.date === '未指定' ? '' : order.date,
            time: order.time,
            shoesDetail: order.shoesDetail,
            subtotal: order.subtotal.toFixed(2),
            total: order.total.toFixed(2),
            remark: order.remark,
            createdAt: new Date().toISOString()
        };
        try {
            const res = await axios.post(`${apiUrl}?action=addOrder`, orderData);
            if (res.data.success) {
                alert('✅ 订单提交成功！商家会尽快联系您。');
                saveCustomerInfo();
                document.getElementById('orderModal').style.display = 'none';
                document.getElementById('name').value = '';
                document.getElementById('phone').value = '';
                document.getElementById('address').value = '';
                document.getElementById('date').value = '';
                document.getElementById('remark').value = '';
                document.getElementById('urgent').value = '';
                initShoes();
                refreshTotal();
            } else {
                alert('提交失败，请稍后重试');
            }
        } catch { alert('提交失败'); }
    }

    async function queryOrdersByPhone(phone) {
        const res = await axios.get(`${apiUrl}?action=getOrders`);
        const allOrders = res.data;
        const myOrders = allOrders.filter(o => o.phone === phone).sort((a,b) => new Date(b.createdAt) - new Date(a.createdAt));
        const totalSpent = myOrders.filter(o => o.status === 'completed').reduce((sum, o) => sum + parseFloat(o.total), 0);
        const listDiv = document.getElementById('orderHistoryList');
        if (!myOrders.length) {
            listDiv.innerHTML = '<p>未找到订单</p>';
            return;
        }
        listDiv.innerHTML = `
            <div style="margin-bottom: 16px; padding: 12px; background: #e6f7e6; border-radius: 20px;">
                <strong>📊 累计消费：¥${totalSpent.toFixed(2)}</strong>
            </div>
            ${myOrders.map(order => {
                let statusText = '', statusClass = '';
                if (order.status === 'pending') { statusText = '待处理'; statusClass = 'status-pending'; }
                else if (order.status === 'processing') { statusText = '处理中'; statusClass = 'status-processing'; }
                else if (order.status === 'completed') { statusText = '已完成'; statusClass = 'status-completed'; }
                else if (order.status === 'after_sale') { statusText = '售后中'; statusClass = 'status-after_sale'; }
                return `
                    <div class="order-history-item">
                        <div><strong>下单时间：</strong> ${order.createdAt}</div>
                        <div><strong>鞋子明细：</strong> ${order.shoesDetail}</div>
                        <div><strong>实付：</strong> ${order.total}元</div>
                        <div><strong>状态：</strong> <span class="status-badge ${statusClass}">${statusText}</span></div>
                        <div><strong>备注：</strong> ${escapeHtml(order.remark)}</div>
                    </div>
                `;
            }).join('')}
        `;
    }

    function bindQueryEvents() {
        const showBtn = document.getElementById('showOrdersBtn');
        const queryDiv = document.getElementById('orderHistory');
        const doQueryBtn = document.getElementById('doQueryBtn');
        showBtn.addEventListener('click', () => {
            if (queryDiv.style.display === 'none') queryDiv.style.display = 'block';
            else queryDiv.style.display = 'none';
        });
        doQueryBtn.addEventListener('click', () => {
            const phone = document.getElementById('queryPhone').value.trim();
            if (!phone) { alert('请输入手机号'); return; }
            queryOrdersByPhone(phone);
        });
    }

    function bindRemarkChips() {
        document.querySelectorAll('.remark-chip').forEach(chip => {
            chip.addEventListener('click', () => {
                const remarkText = document.getElementById('remark');
                const add = chip.getAttribute('data-remark');
                remarkText.value = remarkText.value ? remarkText.value + '；' + add : add;
            });
        });
    }

    function escapeHtml(str) { return str ? str.replace(/[&<>]/g, m => ({ '&':'&amp;', '<':'&lt;', '>':'&gt;' }[m])) : ''; }

    document.getElementById('addShoeBtn').addEventListener('click', () => addShoeRow(0, 1));
    document.getElementById('previewBtn').addEventListener('click', showModal);
    document.getElementById('closeModalBtn').addEventListener('click', () => { document.getElementById('orderModal').style.display = 'none'; });
    document.getElementById('confirmOrderBtn').addEventListener('click', submitOrder);
    bindRemarkChips();
    bindQueryEvents();
    loadConfig();
</script>
</body>
</html>