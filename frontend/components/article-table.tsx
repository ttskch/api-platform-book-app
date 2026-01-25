"use client";

import { Badge, Button, Group, Pagination, Select, Table } from "@mantine/core";
import { useRouter, useSearchParams } from "next/navigation";
import createFetchClient from "openapi-fetch";
import createClient from "openapi-react-query";
import { components, paths } from "../lib/api/schema";

// ブラウザ用のAPIクライアント
const fetchClient = createFetchClient<paths, "application/ld+json">({
  baseUrl: "http://localhost:8000",
  headers: {
    Accept: "application/ld+json",
    "Content-Type": "application/ld+json",
  },
});
const $api = createClient(fetchClient);

type Article =
  | components["schemas"]["Article.jsonld-article.read.list"]
  | components["schemas"]["Article.jsonld-article.read.item"];

type Props = {
  initialArticles: Article[];
  initialPage: number;
  initialItemsPerPage: number;
  initialTotalItems: number;
};

export default function ArticleTable({
  initialArticles,
  initialPage,
  initialItemsPerPage,
  initialTotalItems,
}: Props) {
  const router = useRouter();
  const searchParams = useSearchParams();
  const page = Number(searchParams.get("page")) || 1;
  const itemsPerPage =
    Number(searchParams.get("itemsPerPage")) || initialItemsPerPage;

  const { data, refetch } = $api.useQuery("get", "/api/articles", {
    params: {
      query: { page, itemsPerPage },
    },
  });

  const { mutate } = $api.useMutation("post", "/api/articles", {
    onSuccess: () => refetch(),
  });

  const articles =
    data?.member ?? (page === initialPage ? initialArticles : []);
  const totalItems = data?.totalItems ?? initialTotalItems;

  const setPage = (page: number) => {
    const params = new URLSearchParams(searchParams.toString());
    params.set("page", String(page));
    router.push(`?${params.toString()}`);
  };

  const setItemsPerPage = (itemsPerPage: string | null) => {
    if (!itemsPerPage) return;
    const params = new URLSearchParams(searchParams.toString());
    params.set("itemsPerPage", itemsPerPage);
    params.set("page", "1");
    router.push(`?${params.toString()}`);
  };

  const postArticle = () => {
    mutate({
      body: {
        title: "SPAから作成",
        date: new Date().toLocaleDateString("sv-SE"), // YYYY-MM-DD
      },
    });
  };

  return (
    <>
      <Group justify="flex-end" mb="lg">
        <Button onClick={() => refetch()}>リロード</Button>
        <Button onClick={postArticle}>新規作成</Button>
      </Group>
      <Table mb="lg">
        <Table.Thead>
          <Table.Tr>
            <Table.Th>ID</Table.Th>
            <Table.Th>タイトル</Table.Th>
            <Table.Th>投稿日</Table.Th>
            <Table.Th>公開済み</Table.Th>
          </Table.Tr>
        </Table.Thead>
        <Table.Tbody>
          {articles.map((article) => (
            <Table.Tr key={article.id}>
              <Table.Td>{article.id}</Table.Td>
              <Table.Td>{article.title}</Table.Td>
              <Table.Td>{article.date}</Table.Td>
              <Table.Td>
                {article.published ? (
                  <Badge color="blue">Yes</Badge>
                ) : (
                  <Badge color="gray">No</Badge>
                )}
              </Table.Td>
            </Table.Tr>
          ))}
        </Table.Tbody>
      </Table>
      <Group justify="space-between">
        <Pagination
          total={Math.ceil(totalItems / itemsPerPage)}
          value={page}
          onChange={setPage}
        />
        <Group gap="xs">
          <Select
            data={["10", "20", "30"]}
            value={String(itemsPerPage)}
            onChange={setItemsPerPage}
            w={80}
          />
          件 / ページ
        </Group>
      </Group>
    </>
  );
}
