import React, { useState } from 'react';
import {
  Box,
  Grid,
  Card,
  CardContent,
  Typography,
  Paper,
  Button,
  IconButton,
  Chip,
  Dialog,
  DialogTitle,
  DialogContent,
  DialogActions,
  TextField,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  LinearProgress,
} from '@mui/material';
import {
  AttachMoney as MoneyIcon,
  TrendingUp as TrendingUpIcon,
  TrendingDown as TrendingDownIcon,
  Receipt as ReceiptIcon,
  Payment as PaymentIcon,
  Assessment as AssessmentIcon,
} from '@mui/icons-material';

const Finance: React.FC = () => {
  const [selectedPeriod, setSelectedPeriod] = useState('month');

  // Sample financial data
  const financialData = {
    totalRevenue: 125000,
    monthlyRevenue: 15000,
    pendingPayments: 8500,
    totalExpenses: 45000,
    netProfit: 80000,
    revenueGrowth: 12.5,
    expenseGrowth: -3.2,
  };

  const transactions = [
    {
      id: 1,
      date: '2024-01-15',
      type: 'payment',
      description: 'Payment from John Doe - Consultation',
      amount: 150,
      status: 'completed',
      method: 'Credit Card'
    },
    {
      id: 2,
      date: '2024-01-14',
      type: 'payment',
      description: 'Payment from Jane Smith - Blood Test',
      amount: 75,
      status: 'completed',
      method: 'Insurance'
    },
    {
      id: 3,
      date: '2024-01-13',
      type: 'payment',
      description: 'Payment from Bob Wilson - X-Ray',
      amount: 120,
      status: 'pending',
      method: 'Cash'
    },
    {
      id: 4,
      date: '2024-01-12',
      type: 'expense',
      description: 'Medical Supplies Purchase',
      amount: -500,
      status: 'completed',
      method: 'Bank Transfer'
    },
    {
      id: 5,
      date: '2024-01-11',
      type: 'payment',
      description: 'Payment from Alice Johnson - Physical Therapy',
      amount: 200,
      status: 'completed',
      method: 'Credit Card'
    },
  ];

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'completed': return 'success';
      case 'pending': return 'warning';
      case 'failed': return 'error';
      default: return 'default';
    }
  };

  const getTypeColor = (type: string) => {
    return type === 'payment' ? 'success' : 'error';
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD',
    }).format(amount);
  };

  return (
    <Box>
      <Box display="flex" justifyContent="space-between" alignItems="center" mb={3}>
        <Typography variant="h4">
          Finance
        </Typography>
        <FormControl sx={{ minWidth: 120 }}>
          <InputLabel>Period</InputLabel>
          <Select
            value={selectedPeriod}
            label="Period"
            onChange={(e) => setSelectedPeriod(e.target.value)}
          >
            <MenuItem value="week">This Week</MenuItem>
            <MenuItem value="month">This Month</MenuItem>
            <MenuItem value="quarter">This Quarter</MenuItem>
            <MenuItem value="year">This Year</MenuItem>
          </Select>
        </FormControl>
      </Box>

      <Grid container spacing={3}>
        {/* Financial Overview Cards */}
        <Grid item xs={12} md={3}>
          <Card>
            <CardContent>
              <Box display="flex" alignItems="center" justifyContent="space-between">
                <Box>
                  <Typography color="textSecondary" gutterBottom variant="h6">
                    Total Revenue
                  </Typography>
                  <Typography variant="h4" component="h2">
                    {formatCurrency(financialData.totalRevenue)}
                  </Typography>
                  <Box display="flex" alignItems="center" mt={1}>
                    <TrendingUpIcon sx={{ color: 'success.main', mr: 0.5 }} />
                    <Typography variant="body2" color="success.main">
                      +{financialData.revenueGrowth}%
                    </Typography>
                  </Box>
                </Box>
                <Box
                  sx={{
                    backgroundColor: '#1976d2',
                    borderRadius: '50%',
                    width: 56,
                    height: 56,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    color: 'white',
                  }}
                >
                  <MoneyIcon />
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} md={3}>
          <Card>
            <CardContent>
              <Box display="flex" alignItems="center" justifyContent="space-between">
                <Box>
                  <Typography color="textSecondary" gutterBottom variant="h6">
                    Monthly Revenue
                  </Typography>
                  <Typography variant="h4" component="h2">
                    {formatCurrency(financialData.monthlyRevenue)}
                  </Typography>
                  <Box display="flex" alignItems="center" mt={1}>
                    <TrendingUpIcon sx={{ color: 'success.main', mr: 0.5 }} />
                    <Typography variant="body2" color="success.main">
                      +8.2%
                    </Typography>
                  </Box>
                </Box>
                <Box
                  sx={{
                    backgroundColor: '#388e3c',
                    borderRadius: '50%',
                    width: 56,
                    height: 56,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    color: 'white',
                  }}
                >
                  <ReceiptIcon />
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} md={3}>
          <Card>
            <CardContent>
              <Box display="flex" alignItems="center" justifyContent="space-between">
                <Box>
                  <Typography color="textSecondary" gutterBottom variant="h6">
                    Pending Payments
                  </Typography>
                  <Typography variant="h4" component="h2">
                    {formatCurrency(financialData.pendingPayments)}
                  </Typography>
                  <Typography variant="body2" color="text.secondary">
                    12 transactions
                  </Typography>
                </Box>
                <Box
                  sx={{
                    backgroundColor: '#f57c00',
                    borderRadius: '50%',
                    width: 56,
                    height: 56,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    color: 'white',
                  }}
                >
                  <PaymentIcon />
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        <Grid item xs={12} md={3}>
          <Card>
            <CardContent>
              <Box display="flex" alignItems="center" justifyContent="space-between">
                <Box>
                  <Typography color="textSecondary" gutterBottom variant="h6">
                    Net Profit
                  </Typography>
                  <Typography variant="h4" component="h2">
                    {formatCurrency(financialData.netProfit)}
                  </Typography>
                  <Box display="flex" alignItems="center" mt={1}>
                    <TrendingDownIcon sx={{ color: 'error.main', mr: 0.5 }} />
                    <Typography variant="body2" color="error.main">
                      {financialData.expenseGrowth}%
                    </Typography>
                  </Box>
                </Box>
                <Box
                  sx={{
                    backgroundColor: '#dc004e',
                    borderRadius: '50%',
                    width: 56,
                    height: 56,
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    color: 'white',
                  }}
                >
                  <AssessmentIcon />
                </Box>
              </Box>
            </CardContent>
          </Card>
        </Grid>

        {/* Revenue Chart Placeholder */}
        <Grid item xs={12} md={8}>
          <Paper sx={{ p: 2 }}>
            <Typography variant="h6" gutterBottom>
              Revenue Trend
            </Typography>
            <Box sx={{ height: 300, display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
              <Typography color="text.secondary">
                Revenue chart will be displayed here
              </Typography>
            </Box>
          </Paper>
        </Grid>

        {/* Payment Methods */}
        <Grid item xs={12} md={4}>
          <Paper sx={{ p: 2 }}>
            <Typography variant="h6" gutterBottom>
              Payment Methods
            </Typography>
            <Box mb={2}>
              <Box display="flex" justifyContent="space-between" mb={1}>
                <Typography variant="body2">Credit Card</Typography>
                <Typography variant="body2">65%</Typography>
              </Box>
              <LinearProgress variant="determinate" value={65} />
            </Box>
            <Box mb={2}>
              <Box display="flex" justifyContent="space-between" mb={1}>
                <Typography variant="body2">Insurance</Typography>
                <Typography variant="body2">25%</Typography>
              </Box>
              <LinearProgress variant="determinate" value={25} />
            </Box>
            <Box mb={2}>
              <Box display="flex" justifyContent="space-between" mb={1}>
                <Typography variant="body2">Cash</Typography>
                <Typography variant="body2">10%</Typography>
              </Box>
              <LinearProgress variant="determinate" value={10} />
            </Box>
          </Paper>
        </Grid>

        {/* Recent Transactions */}
        <Grid item xs={12}>
          <Paper sx={{ p: 2 }}>
            <Typography variant="h6" gutterBottom>
              Recent Transactions
            </Typography>
            
            <TableContainer>
              <Table>
                <TableHead>
                  <TableRow>
                    <TableCell>Date</TableCell>
                    <TableCell>Description</TableCell>
                    <TableCell>Amount</TableCell>
                    <TableCell>Method</TableCell>
                    <TableCell>Status</TableCell>
                    <TableCell>Actions</TableCell>
                  </TableRow>
                </TableHead>
                <TableBody>
                  {transactions.map((transaction) => (
                    <TableRow key={transaction.id}>
                      <TableCell>
                        {new Date(transaction.date).toLocaleDateString()}
                      </TableCell>
                      <TableCell>{transaction.description}</TableCell>
                      <TableCell>
                        <Typography
                          variant="subtitle1"
                          fontWeight="bold"
                          color={transaction.amount > 0 ? 'success.main' : 'error.main'}
                        >
                          {formatCurrency(transaction.amount)}
                        </Typography>
                      </TableCell>
                      <TableCell>{transaction.method}</TableCell>
                      <TableCell>
                        <Chip
                          label={transaction.status}
                          color={getStatusColor(transaction.status) as any}
                          size="small"
                        />
                      </TableCell>
                      <TableCell>
                        <IconButton size="small">
                          <ReceiptIcon />
                        </IconButton>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </TableContainer>
          </Paper>
        </Grid>
      </Grid>
    </Box>
  );
};

export default Finance;
