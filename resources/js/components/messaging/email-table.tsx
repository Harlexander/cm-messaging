import { useState } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card } from '@/components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { ArrowUpDown } from 'lucide-react';
import {
    ColumnDef,
    ColumnFiltersState,
    SortingState,
    flexRender,
    getCoreRowModel,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    useReactTable,
} from '@tanstack/react-table';
import moment from 'moment';
import { Link } from '@inertiajs/react';

interface EmailMessage {
    id: number;
    subject: string;
    message: string;
    sent_at: string;
    delivered_count: number;
    opened_count: number;
    click_count: number;
}

interface EmailTableProps {
    messages: EmailMessage[];
    onRowClick?: (email: EmailMessage) => void;
}

export function EmailTable({ messages, onRowClick }: EmailTableProps) {
    const [sorting, setSorting] = useState<SortingState>([]);
    const [columnFilters, setColumnFilters] = useState<ColumnFiltersState>([]);

    const columns: ColumnDef<EmailMessage>[] = [
        {
            accessorKey: 'subject',
            header: ({ column }) => (
                <Button
                    variant="ghost"
                    onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                >
                    Subject
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            ),
        },
        {
            accessorKey: 'message',
            header: 'Message',
            cell: ({ row }) => {
                const message = row.getValue('message') as string;
                return <div style={{ maxWidth: '300px', overflow: 'hidden', textOverflow: 'ellipsis' }} dangerouslySetInnerHTML={{ __html: message }} />;
            },
        },
        {
            accessorKey: 'created_at',
            header: ({ column }) => (
                <Button
                    variant="ghost"
                    onClick={() => column.toggleSorting(column.getIsSorted() === 'asc')}
                >
                    Sent At
                    <ArrowUpDown className="ml-2 h-4 w-4" />
                </Button>
            ),
            cell: ({ row }) => {
                const createdAt = row.getValue('created_at') as string;
                return <div>{moment(createdAt).format('MMMM D, YYYY h:mm A')}</div>;
            },
        },
        {
            accessorKey: 'delivered_count',
            header: 'Delivered',
        },
        {
            accessorKey: 'opened_count',
            header: 'Opened',
            cell: ({ row }) => {
                const delivered = row.getValue('delivered_count') as number;
                const opened = row.getValue('opened_count') as number;
                const openRate = delivered > 0 ? (opened / delivered) * 100 : 0;
                return (
                    <div className="flex flex-col">
                        <span>{opened}</span>
                        <span className="text-xs text-muted-foreground">
                            ({openRate.toFixed(1)}%)
                        </span>
                    </div>
                );
            },
        },
        {
            id: "actions",
            cell: ({row}) => (
                <Link href={`/messaging/email/${row.original.id}`}><Button>View</Button></Link>
            )
        }
    ];

    const table = useReactTable({
        data: messages,
        columns,
        getCoreRowModel: getCoreRowModel(),
        onSortingChange: setSorting,
        getSortedRowModel: getSortedRowModel(),
        onColumnFiltersChange: setColumnFilters,
        getFilteredRowModel: getFilteredRowModel(),
        getPaginationRowModel: getPaginationRowModel(),
        state: {
            sorting,
            columnFilters,
        },
    });

    return (
        <>
            <div className="flex items-center gap-4 mb-4">
                <Input
                    placeholder="Search emails..."
                    value={(table.getColumn('subject')?.getFilterValue() as string) ?? ''}
                    onChange={(e) =>
                        table.getColumn('subject')?.setFilterValue(e.target.value)
                    }
                    className="max-w-sm"
                />
            </div>

            <Card>
                <Table>
                    <TableHeader>
                        {table.getHeaderGroups().map((headerGroup) => (
                            <TableRow key={headerGroup.id}>
                                {headerGroup.headers.map((header) => (
                                    <TableHead key={header.id}>
                                        {header.isPlaceholder
                                            ? null
                                            : flexRender(
                                                header.column.columnDef.header,
                                                header.getContext()
                                            )}
                                    </TableHead>
                                ))}
                            </TableRow>
                        ))}
                    </TableHeader>
                    <TableBody>
                        {table.getRowModel().rows?.length ? (
                            table.getRowModel().rows.map((row) => (
                                <TableRow key={row.id}>
                                    {row.getVisibleCells().map((cell) => (
                                        <TableCell key={cell.id}>
                                            {flexRender(
                                                cell.column.columnDef.cell,
                                                cell.getContext()
                                            )}
                                        </TableCell>
                                    ))}
                                </TableRow>
                            ))
                        ) : (
                            <TableRow>
                                <TableCell
                                    colSpan={columns.length}
                                    className="h-24 text-center"
                                >
                                    No emails found.
                                </TableCell>
                            </TableRow>
                        )}
                    </TableBody>
                </Table>
            </Card>

            <div className="flex items-center justify-end space-x-2 py-4">
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => table.previousPage()}
                    disabled={!table.getCanPreviousPage()}
                >
                    Previous
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => table.nextPage()}
                    disabled={!table.getCanNextPage()}
                >
                    Next
                </Button>
            </div>
        </>
    );
} 